<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for User store and update operations
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admins, Dosen, and Super Admin can create/update users
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'dosen', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'name' => $isUpdate ? ['sometimes', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'email' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'email',
                'max:255',
                $isUpdate ? Rule::unique('users')->ignore($userId) : 'unique:users',
            ],
            'password' => $isUpdate ? ['nullable', 'string', 'min:8', 'confirmed'] : ['required', 'string', 'min:8', 'confirmed'],
            'role' => $isUpdate ? ['sometimes', 'string', 'in:admin,dosen,student,super_admin,prodi_admin,maba'] : ['required', 'string', 'in:admin,dosen,student,super_admin,prodi_admin,maba'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
            'major_id' => ['nullable', 'string', 'exists:majors,code'],
            'student_id' => [
                'nullable',
                'string',
                'max:50',
                $isUpdate ? Rule::unique('users')->ignore($userId) : 'unique:users',
            ],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:4'],
            'enrollment_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'graduation_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'badges' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Full name',
            'email' => 'Email address',
            'password' => 'Password',
            'role' => 'User role',
            'faculty_id' => 'Faculty',
            'major_id' => 'Major',
            'student_id' => 'Student ID',
            'gpa' => 'GPA',
            'enrollment_year' => 'Enrollment year',
            'graduation_year' => 'Graduation year',
            'phone' => 'Phone number',
            'address' => 'Address',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
            'student_id.unique' => 'A user with this student ID already exists.',
            'role.in' => 'Role must be one of: admin, dosen, student, super_admin, prodi_admin, or maba.',
            'gpa.min' => 'GPA cannot be less than 0.',
            'gpa.max' => 'GPA cannot be greater than 4.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Remove password from data if it's empty during update
        if (($this->isMethod('PUT') || $this->isMethod('PATCH')) && $this->password === '') {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}
