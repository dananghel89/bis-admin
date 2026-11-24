<?php

namespace App\Http\Requests;

use App\Support\PdfUpload;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Staff adding a license (a PDF is required) or editing one (a new PDF replaces the old).
 */
class LicenseRequest extends FormRequest
{
    /**
     * The route's `can:manageProperties` already checked the permission.
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
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'title' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:100'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'file' => PdfUpload::rules(! $this->route('license')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return PdfUpload::messages() + [
            'expires_at.after_or_equal' => 'The expiry date can\'t be before the issue date.',
        ];
    }
}
