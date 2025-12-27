<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for Major store and update operations
 */
class MajorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admins and Faculty can create/update majors
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
        $majorId = $this->route('major') ?? $this->route('id');

        return [
            'faculty_id' => ['required', 'exists:faculties,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                $isUpdate ? Rule::unique('majors')->ignore($majorId) : 'unique:majors',
            ],
            'description' => ['nullable', 'string'],
            'head_of_program' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'duration_years' => ['nullable', 'integer', 'min:1', 'max:10'],
            'credit_hours' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'faculty_id' => 'Faculty',
            'name' => 'Major name',
            'code' => 'Major code',
            'description' => 'Description',
            'head_of_program' => 'Head of program',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'duration_years' => 'Duration in years',
            'credit_hours' => 'Credit hours',
            'is_active' => 'Active status',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'faculty_id.required' => 'Faculty is required.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
            'name.required' => 'Major name is required.',
            'code.required' => 'Major code is required.',
            'code.unique' => 'A major with this code already exists.',
            'email.email' => 'Please provide a valid email address.',
            'duration_years.min' => 'Duration must be at least 1 year.',
            'duration_years.max' => 'Duration cannot exceed 10 years.',
            'credit_hours.min' => 'Credit hours must be at least 1.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }
}