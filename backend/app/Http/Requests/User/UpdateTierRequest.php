<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tier' => 'required|in:visitor,member,partnership',
            'reason' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tier.required' => 'Tier is required',
            'tier.in' => 'Invalid tier selected',
            'reason.required' => 'Reason is required',
        ];
    }
}
