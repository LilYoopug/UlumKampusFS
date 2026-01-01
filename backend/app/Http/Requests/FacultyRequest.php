<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for Faculty store and update operations
 */
class FacultyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admins and Faculty can create/update faculties
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'faculty']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $facultyId = $this->route('faculty') ?? $this->route('id');

        return [
            'id' => ['nullable', 'string', 'max:50', 'unique:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                $isUpdate ? Rule::unique('faculties')->ignore($facultyId) : 'unique:faculties',
            ],
            'description' => ['nullable', 'string'],
            'dean_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Faculty name',
            'code' => 'Faculty code',
            'description' => 'Description',
            'dean_name' => 'Dean name',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'is_active' => 'Active status',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Faculty name is required.',
            'code.required' => 'Faculty code is required.',
            'code.unique' => 'A faculty with this code already exists.',
            'email.email' => 'Please provide a valid email address.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }
}
