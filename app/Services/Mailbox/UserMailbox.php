<?php

namespace App\Services\Mailbox;

use App\Models\MailAccount;
use DirectoryTree\ImapEngine\FolderInterface;
use DirectoryTree\ImapEngine\MailboxInterface;
use DirectoryTree\ImapEngine\MessageInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * One user's mailbox for Alerts: list, read and send email over their own
 * IMAP/SMTP account. First version: no attachments.
 */
class UserMailbox
{
    public const BOXES = ['inbox' => 'Inbox', 'sent' => 'Sent'];

    /** Folder names to try when the server doesn't flag its Sent folder. */
    protected const SENT_NAMES = ['Sent', 'Sent Items', 'Sent Messages', 'Sent Mail', 'INBOX.Sent', 'INBOX/Sent', '[Gmail]/Sent Mail'];

    /** These servers save a copy of sent mail themselves; appending one would duplicate it. */
    protected const SAVES_SENT_ITSELF = ['gmail.com', 'googlemail.com', 'office365.com', 'outlook.com'];

    protected ?MailboxInterface $imap = null;

    public function __construct(protected MailAccount $account, protected MailboxConnector $connector) {}

    public function account(): MailAccount
    {
        return $this->account;
    }

    /**
     * A page of messages in a box, newest first, with headers and flags only.
     *
     * @return LengthAwarePaginator<int, MessageInterface>
     */
    public function messages(string $box, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->attempt(function () use ($box, $page, $perPage) {
            $folder = $this->folder($box);

            if (! $folder) {
                return new LengthAwarePaginator([], 0, $perPage, $page);
            }

            $result = $folder->messages()->withHeaders()->withFlags()->newest()->paginate($perPage, $page);

            // ImapEngine has its own paginator; the views use Laravel's (links(), Bootstrap 5).
            return new LengthAwarePaginator($result->items()->all(), $result->total(), $result->perPage(), $result->currentPage());
        });
    }

    /**
     * One message with its body. Opening an unread inbox message marks it seen.
     */
    public function message(string $box, int $uid): ?MessageInterface
    {
        return $this->attempt(function () use ($box, $uid) {
            $message = $this->folder($box)?->messages()->withHeaders()->withBody()->withFlags()->find($uid);

            if ($message && $box === 'inbox' && ! $message->isSeen()) {
                $message->markSeen();
            }

            return $message;
        });
    }

    /**
     * Send a plain-text email from the account's address, then keep a copy in the
     * Sent folder (unless the server does that itself).
     *
     * @param  list<string>  $to
     * @param  list<string>  $cc
     */
    public function send(string $fromName, array $to, array $cc, string $subject, string $body, ?MessageInterface $replyTo = null): void
    {
        $email = (new Email)
            ->from(new Address($this->account->email, $fromName))
            ->to(...$to)
            ->subject($subject)
            ->text($body);

        if ($cc) {
            $email->cc(...$cc);
        }

        if ($replyTo?->messageId()) {
            $id = '<'.trim($replyTo->messageId(), '<>').'>';
            $email->getHeaders()->addIdHeader('In-Reply-To', trim($id, '<>'));
            $email->getHeaders()->addTextHeader('References', $id);
        }

        try {
            $sent = $this->connector->smtp($this->account)->send($email);
        } catch (Throwable $e) {
            report($e);

            throw new MailboxException('The email could not be sent. Check your SMTP settings, then try again.', 0, $e);
        }

        if ($sent && ! Str::endsWith(strtolower($this->account->smtp_host), self::SAVES_SENT_ITSELF)) {
            try {
                $this->folder('sent')?->messages()->append($sent->toString(), ['\\Seen']);
            } catch (Throwable $e) {
                report($e); // sent anyway; only the copy is missing
            }
        }
    }

    /**
     * Try both servers. Returns [imap error or null, smtp error or null].
     *
     * @return array{imap: ?string, smtp: ?string}
     */
    public function test(): array
    {
        $result = ['imap' => null, 'smtp' => null];

        try {
            $this->imap()->inbox();
        } catch (Throwable $e) {
            $result['imap'] = $this->reason($e);
        }

        try {
            $transport = $this->connector->smtp($this->account);
            if (method_exists($transport, 'start')) {
                $transport->start();
                $transport->stop();
            }
        } catch (Throwable $e) {
            $result['smtp'] = $this->reason($e);
        }

        return $result;
    }

    /**
     * The folder for a box: the inbox, or the Sent folder (by its \Sent flag, else by name).
     */
    public function folder(string $box): ?FolderInterface
    {
        if ($box === 'inbox') {
            return $this->imap()->inbox();
        }

        $folders = $this->imap()->folders();

        foreach ($folders->get() as $folder) {
            if (in_array('\\sent', array_map('strtolower', $folder->flags()), true)) {
                return $folder;
            }
        }

        foreach (self::SENT_NAMES as $name) {
            if ($folder = $folders->find($name)) {
                return $folder;
            }
        }

        return null;
    }

    protected function imap(): MailboxInterface
    {
        return $this->imap ??= $this->connector->imap($this->account);
    }

    /**
     * Run an IMAP call; any failure becomes a MailboxException with a safe message.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function attempt(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (MailboxException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new MailboxException('Could not read your mailbox: '.$this->reason($e), 0, $e);
        }
    }

    /**
     * A short reason for people, without anything secret.
     */
    protected function reason(Throwable $e): string
    {
        $message = Str::of($e->getMessage())->replace((string) $this->account->password, '***')->squish()->limit(200);

        return $message->isEmpty() ? class_basename($e) : (string) $message;
    }
}
