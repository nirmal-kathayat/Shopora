<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesRequest extends FormRequest
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
            'order_by' => 'nullable',
            'customer_id' => 'nullable',
            'discount' => 'nullable|numeric|min:0',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer',
            'products.*.qty' => 'required|integer|min:1',
            'products.*.payment_mode' => 'nullable|string',
            'products.*.price_per_unit' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable',
            'split_payments' => 'nullable|array',
            'split_payments.*.payment_mode_id' => 'required|integer',
            'split_payments.*.amount' => 'required|numeric|min:0'
        ];
    }
}
