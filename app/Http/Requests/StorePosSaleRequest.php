<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_type' => 'required|in:cash,card,credit',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_type' => 'required|in:base,wholesale',
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
}
