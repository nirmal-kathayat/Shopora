<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseInventoryRequest extends FormRequest
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
            'vendor_name' => 'required|string|max:255',
            'bill_date' => 'required|date',
            'address' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:50',
            'vat_amount' => 'nullable|numeric|min:0',
            'inventory_items' => 'required|array|min:1',
            'inventory_items.*.inventory_item_id' => 'required|integer|exists:inventory_items,id',
            'inventory_items.*.qty' => 'required|numeric|min:0',
            'inventory_items.*.rate' => 'required|numeric|min:0'
        ];
    }
}
