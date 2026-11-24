<?php

namespace App\Http\Requests;

use App\Models\MailAccount;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A user's mail settings for Alerts. Editing them, an empty password keeps the
 * stored one.
 */
class MailAccountRequest extends FormRequest
{
    /**
     * The routes check the permission (`editProfile` for your own, `manageUsers`
     * for someone else's).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Whose settings these are: the user in the route, or the logged-in user.
     */
    public function owner(): User
    {
        return $this->route('user') ?? $this->user();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $encryptions = Rule::in(array_keys(MailAccount::ENCRYPTIONS));

        return [
            'mail_email' => ['required', 'string', 'email', 'max:255'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => [$this->owner()->mailAccount ? 'nullable' : 'required', 'string', 'max:255'],
            'imap_host' => ['required', 'string', 'max:255'],
            'imap_port' => ['required', 'integer', 'between:1,65535'],
            'imap_encryption' => ['required', $encryptions],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_encryption' => ['required', $encryptions],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mail_email' => 'email address',
            'mail_username' => 'username',
            'mail_password' => 'password',
            'imap_host' => 'IMAP server',
            'imap_port' => 'IMAP port',
            'smtp_host' => 'SMTP server',
            'smtp_port' => 'SMTP port',
        ];
    }

    /**
     * The settings as MailAccount attributes; the stored password stays when none was typed.
     *
     * @return array<string, mixed>
     */
    public function accountAttributes(): array
    {
        $data = $this->validated();

        $attributes = [
            'email' => $data['mail_email'],
            'username' => $data['mail_username'] ?? null,
            'imap_host' => $data['imap_host'],
            'imap_port' => (int) $data['imap_port'],
            'imap_encryption' => $data['imap_encryption'],
            'smtp_host' => $data['smtp_host'],
            'smtp_port' => (int) $data['smtp_port'],
            'smtp_encryption' => $data['smtp_encryption'],
        ];

        if (filled($data['mail_password'] ?? null)) {
            $attributes['password'] = $data['mail_password'];
        }

        return $attributes;
    }
}
