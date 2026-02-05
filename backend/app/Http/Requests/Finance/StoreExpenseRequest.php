<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_type_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'required|string',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'expense_type_id.required' => 'Expense type is required',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'expense_date.required' => 'Expense date is required',
            'description.required' => 'Description is required',
            'receipt.mimes' => 'Receipt must be jpg, jpeg, png, or pdf',
            'receipt.max' => 'Receipt must not exceed 5MB',
        ];
    }
}
