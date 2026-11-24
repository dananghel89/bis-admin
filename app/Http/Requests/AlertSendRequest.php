<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Writing an email in Alerts. "To" and "Cc" take one or more addresses,
 * separated by commas or semicolons.
 */
class AlertSendRequest extends FormRequest
{
    /**
     * The route's `can:useMailbox` already checked the permission.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'to' => ['required', 'string', 'max:2000'],
            'cc' => ['nullable', 'string', 'max:2000'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'reply_to' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Every address must be valid.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach (['to', 'cc'] as $field) {
                    foreach ($this->addresses($field) as $address) {
                        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
                            $validator->errors()->add($field, "\"{$address}\" is not a valid email address.");
                        }
                    }
                }
            },
        ];
    }

    /**
     * @return list<string>
     */
    public function addresses(string $field): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,;]+/', (string) $this->input($field)))));
    }
}
