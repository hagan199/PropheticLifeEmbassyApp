<?php

namespace App\Http\Requests\Visitor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|nullable|email',
            'status' => 'sometimes|in:not_contacted,contacted,engaged,converted',
        ];
    }
}
