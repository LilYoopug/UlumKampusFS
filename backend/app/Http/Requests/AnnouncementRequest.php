<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class AnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'in:Akademik,Kampus,Mata Kuliah'],
            'target_audience' => ['nullable', 'in:all,students,faculty,staff,specific_course,specific_faculty'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'allow_comments' => ['nullable', 'boolean'],
            'view_count' => ['nullable', 'integer'],
            'attachment_url' => ['nullable', 'url'],
            'attachment_type' => ['nullable', 'string', 'max:50'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.exists' => 'The selected course does not exist.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
            'created_by.exists' => 'The selected user does not exist.',
            'title.required' => 'The announcement title is required.',
            'title.max' => 'The announcement title may not exceed 255 characters.',
            'content.required' => 'The announcement content is required.',
            'category.in' => 'The category must be one of: Akademik, Kampus, Mata Kuliah.',
            'target_audience.in' => 'The target audience must be one of: all, students, faculty, staff, specific_course, specific_faculty.',
            'priority.in' => 'The priority must be one of: low, normal, high, urgent.',
            'expires_at.after' => 'The expiration date must be in the future.',
            'attachment_url.url' => 'The attachment URL must be a valid URL.',
            'order.min' => 'The order value must be at least 0.',
        ];
    }
}
