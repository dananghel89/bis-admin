<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailAccountRequest;
use App\Models\MailAccount;
use App\Models\User;
use App\Services\Mailbox\MailboxConnector;
use App\Services\Mailbox\UserMailbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Mail settings for Alerts: every user edits their own (Settings page); staff
 * can also edit anyone's (user edit page).
 */
class MailAccountController extends Controller
{
    public function save(MailAccountRequest $request, ?User $user = null): RedirectResponse|JsonResponse
    {
        $owner = $request->owner();
        $account = $owner->mailAccount ?? new MailAccount(['user_id' => $owner->id]);

        $account->fill($request->accountAttributes());
        $account->user()->associate($owner);
        $account->save();

        return $this->done($request, $user ? route('userEdit', $user) : route('settings'), 'Mail settings saved.');
    }

    /**
     * Try the settings in the form (saved or not) and say what works.
     */
    public function test(MailAccountRequest $request, MailboxConnector $connector, ?User $user = null): RedirectResponse|JsonResponse
    {
        $account = ($request->owner()->mailAccount?->replicate() ?? new MailAccount)->fill($request->accountAttributes());
        $result = (new UserMailbox($account, $connector))->test();

        $ok = ! $result['imap'] && ! $result['smtp'];
        $message = $ok
            ? 'Both servers answered: reading (IMAP) and sending (SMTP) work.'
            : collect(['IMAP' => $result['imap'], 'SMTP' => $result['smtp']])
                ->map(fn ($error, $server) => $error ? "{$server} failed: {$error}" : "{$server} works.")
                ->implode(' ');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $ok ? 200 : 422);
        }

        return back()->withInput($request->except('mail_password'))->with($ok ? 'success' : 'error', $message);
    }

    public function delete(Request $request, ?User $user = null): RedirectResponse|JsonResponse
    {
        ($user ?? $request->user())->mailAccount?->delete();

        return $this->done($request, $user ? route('userEdit', $user) : route('settings'), 'Mail settings removed.');
    }
}
