<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for DiscussionThread store and update operations
 */
class DiscussionThreadRequest extends FormRequest
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
        $threadId = $this->route('discussion_thread') ?? $this->route('thread') ?? $this->route('id');

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'module_id' => ['nullable', 'integer', 'exists:course_modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['nullable', 'in:question,discussion,announcement,help'],
            'status' => $isUpdate ? ['nullable', 'in:open,closed,archived'] : ['nullable', 'in:open,closed'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_locked' => $isUpdate ? ['nullable', 'boolean'] : ['nullable', 'boolean'],
            'attachment_url' => ['nullable', 'string', 'max:500'],
            'attachment_type' => ['nullable', 'string', 'max:50'],
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
            'title' => 'Thread title',
            'content' => 'Thread content',
            'type' => 'Thread type',
            'status' => 'Thread status',
            'is_pinned' => 'Pinned status',
            'is_locked' => 'Locked status',
            'attachment_url' => 'Attachment URL',
            'attachment_type' => 'Attachment type',
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
            'title.required' => 'The thread title is required.',
            'title.max' => 'The thread title may not exceed 255 characters.',
            'content.required' => 'The thread content is required.',
            'type.in' => 'Thread type must be one of: question, discussion, announcement, help.',
            'status.in' => 'Status must be one of: open, closed, archived.',
            'attachment_url.max' => 'The attachment URL may not exceed 500 characters.',
            'attachment_type.max' => 'The attachment type may not exceed 50 characters.',
        ];
    }
}