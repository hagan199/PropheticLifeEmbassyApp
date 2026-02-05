<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class BulkRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'string',
            'reason' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Rejection reason is required',
        ];
    }
}
