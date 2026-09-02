<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ph_number' => Customer::normalizePhone($this->input('ph_number')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:191'],
            // The phone doubles as a storefront login identifier, so it has to
            // stay unique and present.
            'ph_number' => [
                'required', 'string', 'digits_between:7,10',
                Rule::unique('customers', 'ph_number')->ignore($this->route('id')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ph_number.unique' => 'A customer with this phone number already exists.',
        ];
    }
}
