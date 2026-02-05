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
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'source' => 'required|in:friend,social_media,walk_in,other',
            'ministry_interests' => 'nullable|array',
            'notes' => 'nullable|string',
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
