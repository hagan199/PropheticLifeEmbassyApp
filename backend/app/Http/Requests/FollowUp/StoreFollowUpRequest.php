<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_id' => 'required|string',
            'contact_method' => 'required|in:whatsapp,sms,call,in_person',
            'notes' => 'required|string',
            'next_follow_up_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_id.required' => 'Visitor is required',
            'contact_method.required' => 'Contact method is required',
            'contact_method.in' => 'Invalid contact method',
            'notes.required' => 'Notes are required',
        ];
    }
}
