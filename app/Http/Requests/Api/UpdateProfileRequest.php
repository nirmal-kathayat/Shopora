<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * What a signed-in customer may change about themselves. Password is not here
 * - that is its own flow, with the current password to prove it.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ph_number' => Customer::normalizePhone($this->input('ph_number')),
            'email' => is_string($this->input('email'))
                ? trim(strtolower($this->input('email')))
                : $this->input('email'),
            // An emptied optional field means "remove it", not "".
            'address' => $this->blankToNull($this->input('address')),
            'pan_number' => $this->blankToNull($this->input('pan_number')),
        ]);
    }

    public function rules(): array
    {
        $id = $this->user()->getKey();

        return [
            'name' => ['required', 'string', 'max:191'],
            // Both of these double as login identifiers, so they stay unique.
            'email' => [
                'required', 'string', 'email', 'max:191',
                Rule::unique('customers', 'email')->ignore($id),
            ],
            'ph_number' => [
                'required', 'string', 'digits_between:7,10',
                Rule::unique('customers', 'ph_number')->ignore($id),
            ],
            'address' => ['nullable', 'string', 'max:191'],
            'pan_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ph_number' => 'phone number',
            'pan_number' => 'PAN number',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Another account already uses this email.',
            'ph_number.unique' => 'Another account already uses this phone number.',
        ];
    }

    private function blankToNull(mixed $value): mixed
    {
        return is_string($value) && trim($value) === '' ? null : $value;
    }
}
