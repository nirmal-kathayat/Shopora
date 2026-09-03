<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            // bcrypt silently ignores anything past 72 bytes, so a longer
            // password would not be the password the customer thinks it is.
            'password' => ['required', 'confirmed', 'max:72', Password::min(8), 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.different' => 'Choose a password you have not just been using.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'current password',
            'password' => 'new password',
        ];
    }
}
