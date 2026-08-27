<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'required',
            "email" => 'required|unique:admins,email,' . $this->id,
            "username" => 'required|unique:admins,username,' . $this->id,
            'password' => $this->id ? 'nullable' : 'required|min:6|confirmed',
        ];
    }
}
