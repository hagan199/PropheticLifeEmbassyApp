<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class RejectAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
