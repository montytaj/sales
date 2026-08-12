<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:individual,company'],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_secondary' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'cr_number' => ['nullable', 'string', 'max:50'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'credit_period_days' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'in:regular,vip,corporate,wholesale'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ];
    }
}
