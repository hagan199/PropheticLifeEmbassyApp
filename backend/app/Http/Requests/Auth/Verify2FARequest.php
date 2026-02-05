<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class Verify2FARequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tempToken' => 'required|string',
            'code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'tempToken.required' => 'Session token is required',
            'code.required' => 'Verification code is required',
            'code.size' => 'Verification code must be 6 digits',
        ];
    }
}
