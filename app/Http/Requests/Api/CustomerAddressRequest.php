<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ph_number' => Customer::normalizePhone($this->input('ph_number')),
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:191'],
            'ph_number' => ['required', 'string', 'digits_between:7,10'],
            'city' => ['required', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
            'street' => ['required', 'string', 'max:191'],
            'landmark' => ['nullable', 'string', 'max:191'],
            'is_default' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ph_number' => 'phone number',
            'recipient_name' => 'full name',
            'street' => 'street address',
        ];
    }
}
