<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for Course store and update operations
 */
class CourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'faculty', 'prodi_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $courseId = $this->route('course') ?? $this->route('id');

        return [
            'id' => [
                'required',
                'string',
                'max:50',
                $isUpdate ? Rule::unique('courses')->ignore($courseId) : 'unique:courses',
            ],
            'faculty_id' => ['required', 'string', 'exists:faculties,id'],
            'major_id' => ['required', 'string', 'exists:majors,code'],
            'instructor_id' => ['nullable', 'integer', 'exists:users,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                $isUpdate ? Rule::unique('courses')->ignore($courseId) : 'unique:courses',
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credit_hours' => ['nullable', 'integer', 'min:1'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'current_enrollment' => ['nullable', 'integer', 'min:0'],
            'semester' => ['nullable', 'string', 'in:Fall,Spring,Summer'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'faculty_id' => 'Faculty',
            'major_id' => 'Major',
            'instructor_id' => 'Instructor',
            'code' => 'Course code',
            'name' => 'Course name',
            'description' => 'Description',
            'credit_hours' => 'Credit hours',
            'capacity' => 'Capacity',
            'current_enrollment' => 'Current enrollment',
            'semester' => 'Semester',
            'year' => 'Year',
            'schedule' => 'Schedule',
            'room' => 'Room',
            'is_active' => 'Active status',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'faculty_id.exists' => 'The selected faculty does not exist.',
            'major_id.exists' => 'The selected major does not exist.',
            'instructor_id.exists' => 'The selected instructor does not exist.',
            'code.unique' => 'A course with this code already exists.',
            'code.max' => 'The course code may not exceed 50 characters.',
            'name.max' => 'The course name may not exceed 255 characters.',
            'credit_hours.min' => 'Credit hours must be at least 1.',
            'capacity.min' => 'Capacity must be at least 1.',
            'current_enrollment.min' => 'Current enrollment cannot be negative.',
            'semester.in' => 'Semester must be one of: Fall, Spring, Summer.',
            'year.min' => 'Year must be at least 2000.',
            'year.max' => 'Year must not exceed 2100.',
            'schedule.max' => 'The schedule may not exceed 255 characters.',
            'room.max' => 'The room may not exceed 100 characters.',
        ];
    }
}
