<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for Grade store and update operations
 */
class GradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
        $gradeId = $this->route('grade') ?? $this->route('id');

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'assignment_id' => ['nullable', 'integer', 'exists:assignments,id'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'grade_letter' => ['nullable', 'string', 'in:A,B,C,D,F'],
            'comments' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'Student',
            'course_id' => 'Course',
            'assignment_id' => 'Assignment',
            'grade' => 'Grade',
            'grade_letter' => 'Grade letter',
            'comments' => 'Comments',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected student does not exist.',
            'course_id.exists' => 'The selected course does not exist.',
            'assignment_id.exists' => 'The selected assignment does not exist.',
            'grade.required' => 'Grade is required.',
            'grade.numeric' => 'Grade must be a number.',
            'grade.min' => 'Grade cannot be less than 0.',
            'grade.max' => 'Grade cannot exceed 100.',
            'grade_letter.in' => 'Grade letter must be one of: A, B, C, D, F.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Add custom validation to ensure the student is enrolled in the course
     * and that the assignment belongs to the course (if provided).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $userId = $this->input('user_id');
            $courseId = $this->input('course_id');
            $assignmentId = $this->input('assignment_id');

            // Ensure student is enrolled in the course
            if ($userId && $courseId) {
                $enrollment = \App\Models\CourseEnrollment::where('student_id', $userId)
                    ->where('course_id', $courseId)
                    ->where('status', 'enrolled')
                    ->first();

                if (!$enrollment) {
                    $validator->errors()->add('user_id', 'The student is not enrolled in this course.');
                }
            }

            // Ensure assignment belongs to the course (if assignment_id is provided)
            if ($assignmentId && $courseId) {
                $assignment = \App\Models\Assignment::where('id', $assignmentId)
                    ->where('course_id', $courseId)
                    ->first();

                if (!$assignment) {
                    $validator->errors()->add('assignment_id', 'The assignment does not belong to this course.');
                }
            }
        });
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-calculate grade letter if not provided but grade is
        if ($this->has('grade') && !$this->has('grade_letter')) {
            $grade = (float) $this->grade;
            $gradeLetter = match (true) {
                $grade >= 90 => 'A',
                $grade >= 80 => 'B',
                $grade >= 70 => 'C',
                $grade >= 60 => 'D',
                default => 'F',
            };
            $this->merge(['grade_letter' => $gradeLetter]);
        }
    }
}