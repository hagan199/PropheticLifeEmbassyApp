<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'role' => 'sometimes|in:admin,pastor,usher,finance,pr_follow_up,department_leader',
            'department_id' => 'sometimes|nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Please provide a valid email address',
            'role.in' => 'Invalid role selected',
        ];
    }
}
