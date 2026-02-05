<?php

namespace App\Http\Requests\Broadcast;

use Illuminate\Foundation\Http\FormRequest;

class StoreBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string',
            'recipient_type' => 'required|in:all,partners,department',
            'department_id' => 'required_if:recipient_type,department|nullable|string',
            'channel' => 'required|in:whatsapp,sms,both',
            'schedule_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Message is required',
            'recipient_type.required' => 'Recipient type is required',
            'recipient_type.in' => 'Invalid recipient type',
            'department_id.required_if' => 'Department is required when sending to a specific department',
            'channel.required' => 'Channel is required',
            'channel.in' => 'Invalid channel selected',
        ];
    }
}
