<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryItemRequest extends FormRequest
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
            'title' => 'required',
            'unit' => 'required',
            'category_id' => 'required',
            'price_per_unit' => 'required|numeric|min:0',
            // Optional "was" price; the storefront only shows it as a discount
            // when it is above the selling price, so it need not exceed it here.
            'compare_at_price' => 'nullable|numeric|min:0',
            'code' => 'required',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ];
    }
}
