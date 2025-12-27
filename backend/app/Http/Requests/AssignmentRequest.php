<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for Assignment store and update operations
 */
class AssignmentRequest extends FormRequest
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
        $assignmentId = $this->route('assignment') ?? $this->route('id');

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'module_id' => ['nullable', 'integer', 'exists:course_modules,id'],
            'created_by' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after:now'],
            'max_points' => ['nullable', 'numeric', 'min:0'],
            'submission_type' => ['nullable', 'in:text,file,link,mixed'],
            'allowed_file_types' => ['nullable', 'string'],
            'max_file_size' => ['nullable', 'integer', 'min:1'],
            'attempts_allowed' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
            'allow_late_submission' => ['nullable', 'boolean'],
            'late_penalty' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'course_id' => 'Course',
            'module_id' => 'Module',
            'created_by' => 'Creator',
            'title' => 'Assignment title',
            'description' => 'Description',
            'instructions' => 'Instructions',
            'due_date' => 'Due date',
            'max_points' => 'Maximum points',
            'submission_type' => 'Submission type',
            'allowed_file_types' => 'Allowed file types',
            'max_file_size' => 'Maximum file size',
            'attempts_allowed' => 'Attempts allowed',
            'is_published' => 'Published status',
            'allow_late_submission' => 'Allow late submission',
            'late_penalty' => 'Late penalty',
            'order' => 'Order',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'course_id.exists' => 'The selected course does not exist.',
            'module_id.exists' => 'The selected module does not exist.',
            'created_by.exists' => 'The selected creator does not exist.',
            'title.max' => 'The assignment title may not exceed 255 characters.',
            'due_date.after' => 'The due date must be in the future.',
            'max_points.min' => 'Maximum points must be at least 0.',
            'submission_type.in' => 'Submission type must be one of: text, file, link, mixed.',
            'max_file_size.min' => 'Maximum file size must be at least 1.',
            'attempts_allowed.min' => 'Attempts allowed must be at least 1.',
            'late_penalty.min' => 'Late penalty must be at least 0.',
            'late_penalty.max' => 'Late penalty must not exceed 100.',
            'order.min' => 'Order must be at least 0.',
        ];
    }
}