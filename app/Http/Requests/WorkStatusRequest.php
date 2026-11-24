<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Staff or an architect who sees the property changing its work status.
 */
class WorkStatusRequest extends FormRequest
{
    /**
     * The route's `can:updateWorkStatus` checked the role. A property this user
     * can't see is a 404, before any validation could hint that it exists.
     */
    public function authorize(): bool
    {
        abort_unless($this->route('property')->isVisibleTo($this->user()), 404);

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'work_status' => ['required', Rule::in(array_keys(Property::WORK_STATUSES)), Rule::notIn([$this->route('property')?->work_status])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'work_status.not_in' => 'Choose a status different from the current one.',
        ];
    }
}
