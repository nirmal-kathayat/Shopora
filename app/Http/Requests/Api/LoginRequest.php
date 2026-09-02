<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'identifier' => is_string($this->input('identifier')) ? trim($this->input('identifier')) : $this->input('identifier'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Either an email address or a phone number - the storefront shows
            // one field and we work out which it is.
            'identifier' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
