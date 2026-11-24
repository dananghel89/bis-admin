<?php

namespace App\Http\Requests;

use App\Support\PdfUpload;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Staff adding a plan (a PDF is required) or editing one (a new PDF replaces the old).
 */
class PlanRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => PdfUpload::rules(! $this->route('plan')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return PdfUpload::messages();
    }
}
