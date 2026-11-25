<?php

namespace Tests\Support;

use App\Models\MailAccount;
use App\Services\Mailbox\MailboxConnector;
use DirectoryTree\ImapEngine\MailboxInterface;
use DirectoryTree\ImapEngine\Testing\FakeFolder;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use RuntimeException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Stands in for the real IMAP/SMTP servers in tests: an in-memory mailbox
 * (ImapEngine's fakes) and an SMTP transport that records what it sends.
 */
class FakeMailboxConnector extends MailboxConnector
{
    public FakeFolder $inbox;

    public FakeFolder $sent;

    /** Set to null for an account without a Trash folder. */
    public ?FakeFolder $trash;

    /** @var list<RawMessage> */
    public array $sentMessages = [];

    public ?string $imapFails = null;

    public ?string $smtpFails = null;

    /** @var list<MailAccount> */
    public array $connectedWith = [];

    public function __construct()
    {
        $this->inbox = new FakeFolder('inbox');
        $this->sent = new FakeFolder('Sent Items', ['\\HasNoChildren', '\\Sent']);
        $this->trash = new FakeFolder('Deleted Items', ['\\HasNoChildren', '\\Trash']);
    }

    /**
     * Put a message in the inbox.
     */
    public function receive(int $uid, string $from, string $subject, string $body, array $flags = [], ?string $html = null, string $messageId = 'abc@example.test'): void
    {
        $date = 'Mon, 23 Nov 2026 10:00:00 +0000';

        $contents = $html === null
            ? "From: {$from}\r\nTo: me@example.test\r\nSubject: {$subject}\r\nDate: {$date}\r\nMessage-ID: <{$messageId}>\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n{$body}"
            : "From: {$from}\r\nTo: me@example.test\r\nSubject: {$subject}\r\nDate: {$date}\r\nMessage-ID: <{$messageId}>\r\nContent-Type: text/html; charset=utf-8\r\n\r\n{$html}";

        $this->inbox->addMessage(new FakeMovableMessage($uid, $flags, $contents));
    }

    public function imap(MailAccount $account): MailboxInterface
    {
        $this->connectedWith[] = $account;

        if ($this->imapFails) {
            throw new RuntimeException($this->imapFails);
        }

        return new FakeMailbox([], array_values(array_filter([$this->inbox, $this->sent, $this->trash])));
    }

    public function smtp(MailAccount $account): TransportInterface
    {
        $connector = $this;

        return new class($connector) implements TransportInterface
        {
            public function __construct(private FakeMailboxConnector $connector) {}

            public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
            {
                if ($this->connector->smtpFails) {
                    throw new RuntimeException($this->connector->smtpFails);
                }

                $this->connector->sentMessages[] = $message;

                return new SentMessage($message, $envelope ?? Envelope::create($message));
            }

            public function __toString(): string
            {
                return 'fake://';
            }
        };
    }
}
