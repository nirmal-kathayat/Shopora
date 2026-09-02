<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // The inventory quick-add modal posts a bare title; only normalise the
        // status when the full form actually sends one.
        if ($this->has('status')) {
            $this->merge(['status' => $this->boolean('status')]);
        }
    }

    public function rules(): array
    {
        return [
            // Two categories with the same name would be indistinguishable in
            // the storefront's aisle list.
            'title' => [
                'required', 'string', 'max:191',
                Rule::unique('categories', 'title')->ignore($this->route('id')),
            ],
            'icon' => ['nullable', Rule::in(array_keys(Category::ICONS))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'image_alt' => ['nullable', 'string', 'max:191'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'A category with this name already exists.',
        ];
    }
}
