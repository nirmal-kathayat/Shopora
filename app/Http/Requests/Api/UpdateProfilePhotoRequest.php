<?php

namespace App\Http\Requests\Api;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.uploaded' => 'The photo must be 2 MB or smaller.',
            'image.max' => 'The photo must be 2 MB or smaller.',
            'image.image' => 'That file is not an image.',
        ];
    }
}
