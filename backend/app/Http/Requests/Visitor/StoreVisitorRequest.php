<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phone' => 'required|string|regex:/^\d{9,15}$/',
            'email' => 'nullable|email',
            'source' => 'required|in:friend,social_media,walk_in,other',
            'ministry_interests' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
            'occupation' => 'nullable|string|max:100|regex:/^[a-zA-Z\s]*$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Visitor name is required',
            'phone.required' => 'Phone number is required',
            'source.required' => 'Source is required',
            'source.in' => 'Invalid source selected',
        ];
    }
}
