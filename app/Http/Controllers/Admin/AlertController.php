<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlertSendRequest;
use App\Services\Mailbox\MailboxConnector;
use App\Services\Mailbox\MailboxException;
use App\Services\Mailbox\UserMailbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Alerts: each user's own mailbox (inbox, sent, read, write, reply) over the
 * IMAP/SMTP account in their mail settings. Without settings, or when the
 * servers fail, the pages say so and link to Settings.
 */
class AlertController extends Controller
{
    public function __construct(protected MailboxConnector $connector) {}

    public function index(Request $request): View
    {
        $box = $this->box($request->query('box'));
        $mailbox = $this->mailbox($request);
        $messages = null;
        $error = null;

        if ($mailbox) {
            try {
                $messages = $mailbox->messages($box, max(1, $request->integer('page', 1)))
                    ->withPath(route('alerts'))
                    ->appends('box', $box);
            } catch (MailboxException $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.alerts.alertsIndex', compact('box', 'mailbox', 'messages', 'error'));
    }

    public function alertView(Request $request, string $box, int $uid): View
    {
        $box = $this->box($box);
        $mailbox = $this->mailbox($request) ?? abort(404);

        try {
            $message = $mailbox->message($box, $uid) ?? abort(404);
        } catch (MailboxException $e) {
            return view('admin.alerts.alertsIndex', ['box' => $box, 'mailbox' => $mailbox, 'messages' => null, 'error' => $e->getMessage()]);
        }

        $html = $message->html();
        $showImages = $request->boolean('images');

        return view('admin.alerts.alertView', compact('box', 'message', 'html', 'showImages'));
    }

    public function alertWrite(Request $request): View|RedirectResponse
    {
        $mailbox = $this->mailbox($request);

        if (! $mailbox) {
            return redirect()->route('alerts');
        }

        $draft = ['to' => '', 'cc' => '', 'subject' => '', 'body' => '', 'reply_to' => null];

        if ($uid = $request->integer('reply')) {
            try {
                $original = $mailbox->message('inbox', $uid);
            } catch (MailboxException) {
                $original = null;
            }

            if ($original) {
                $from = $original->replyTo() ?? $original->from();
                $quoted = collect(preg_split('/\R/', (string) ($original->text() ?? strip_tags((string) $original->html()))))
                    ->map(fn ($line) => '> '.$line)->implode("\n");

                $draft = [
                    'to' => $from?->email() ?? '',
                    'cc' => '',
                    'subject' => Str::startsWith(strtolower((string) $original->subject()), 're:') ? $original->subject() : 'Re: '.$original->subject(),
                    'body' => "\n\n".'On '.$original->date()?->format('Y-m-d H:i').', '.($from?->name() ?: $from?->email()).' wrote:'."\n".$quoted,
                    'reply_to' => $uid,
                ];
            }
        }

        return view('admin.alerts.alertWrite', compact('draft', 'mailbox'));
    }

    public function alertSend(AlertSendRequest $request): RedirectResponse|JsonResponse
    {
        $mailbox = $this->mailbox($request);

        try {
            if (! $mailbox) {
                throw new MailboxException('Set up your mail settings first.');
            }

            $original = $request->validated('reply_to') ? $mailbox->message('inbox', (int) $request->validated('reply_to')) : null;

            $mailbox->send(
                $request->user()->name,
                $request->addresses('to'),
                $request->addresses('cc'),
                $request->validated('subject'),
                $request->validated('body'),
                $original,
            );
        } catch (MailboxException $e) {
            return $request->expectsJson()
                ? response()->json(['message' => $e->getMessage()], 422)
                : back()->withInput()->with('error', $e->getMessage());
        }

        return $this->done($request, route('alerts', ['box' => 'sent']), 'Email sent.');
    }

    /**
     * The user's mailbox, or null when they have no mail settings yet.
     */
    protected function mailbox(Request $request): ?UserMailbox
    {
        $account = $request->user()->mailAccount;

        return $account ? new UserMailbox($account, $this->connector) : null;
    }

    protected function box(mixed $box): string
    {
        return array_key_exists((string) $box, UserMailbox::BOXES) ? $box : 'inbox';
    }
}
