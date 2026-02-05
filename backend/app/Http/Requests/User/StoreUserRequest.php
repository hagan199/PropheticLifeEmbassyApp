<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'role' => 'required|in:admin,pastor,usher,finance,pr_follow_up,department_leader',
            'password' => 'required|string|min:8',
            'department_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'phone.required' => 'Phone number is required',
            'email.email' => 'Please provide a valid email address',
            'role.required' => 'Role is required',
            'role.in' => 'Invalid role selected',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }
}
