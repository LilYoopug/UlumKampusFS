<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FacultyRequest
 *
 * Validates requests for faculty CRUD operations.
 * Ensures data integrity for faculties including:
 * - Required fields (name, code)
 * - Code validation (unique, uppercase format)
 * - Optional fields (description, dean_name, email, phone, is_active)
 */
class FacultyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Admin and Faculty users can create/update faculties.
     * All authenticated users can view faculties.
     */
    public function authorize(): bool
    {
        // For create/update, check if user is admin or faculty
        if ($this->isMethod('POST') || $this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return auth()->check() && in_array(auth()->user()->role, ['admin', 'faculty']);
        }

        // GET requests are allowed for all authenticated users
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $facultyId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'uppercase',
                'regex:/^[A-Z0-9]+$/',
                'unique:faculties,code' . ($facultyId ? ",{$facultyId},id" : ''),
            ],
            'description' => 'nullable|string|max:2000',
            'dean_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Faculty name is required.',
            'name.max' => 'Faculty name must not exceed 255 characters.',
            'code.required' => 'Faculty code is required.',
            'code.max' => 'Faculty code must not exceed 50 characters.',
            'code.uppercase' => 'Faculty code must be in uppercase.',
            'code.regex' => 'Faculty code must contain only uppercase letters and numbers.',
            'code.unique' => 'Faculty code already exists.',
            'description.max' => 'Description must not exceed 2000 characters.',
            'dean_name.max' => 'Dean name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'phone.max' => 'Phone number must not exceed 50 characters.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Faculty Name',
            'code' => 'Faculty Code',
            'description' => 'Description',
            'dean_name' => 'Dean Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'is_active' => 'Active Status',
        ];
    }
}