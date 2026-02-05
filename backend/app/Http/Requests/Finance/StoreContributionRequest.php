<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'contribution_date' => 'required|date',
            'payment_method' => 'required|in:cash,mobile_money,bank_transfer',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be at least 0',
            'contribution_date.required' => 'Contribution date is required',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
        ];
    }
}
