<?php

namespace App\Http\Requests;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Staff adding or editing a property. The work status has its own form
 * (WorkStatusRequest), so every change is logged.
 */
class PropertyRequest extends FormRequest
{
    /**
     * The route's `can:manageProperties` already checked the permission.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // A multi-select with nothing chosen isn't sent at all.
        $this->merge(['architects' => array_values(array_filter((array) $this->input('architects', [])))]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(array_keys(Property::STATUSES))],
            'architects' => ['array'],
            'architects.*' => ['integer', Rule::exists('users', 'id')->where('role', User::EDITOR)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'architects.*.exists' => 'Only architects can be assigned.',
        ];
    }
}
