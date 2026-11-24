<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Staff adding or editing an account. The password is required when adding and
 * optional when editing (empty keeps it).
 */
class UserRequest extends FormRequest
{
    /**
     * The route's `can:manageUsers` already checked the permission.
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', 'integer', Rule::in(array_keys(User::ROLE_LABELS))],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * There must always be a staff account: the last one can't become an architect.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->route('user');

                if ($user?->isStaff() && (int) $this->input('role') !== User::ADMIN
                    && User::where('role', User::ADMIN)->count() === 1) {
                    $validator->errors()->add('role', 'This is the last staff account, so it must stay Staff.');
                }
            },
        ];
    }
}
