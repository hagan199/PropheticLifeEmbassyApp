<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|string',
            'service_type' => 'required|in:friday_night,sunday_service,midweek',
            'service_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'service_type.required' => 'Service type is required',
            'service_type.in' => 'Invalid service type',
            'service_date.required' => 'Service date is required',
            'service_date.date' => 'Invalid date format',
        ];
    }
}
