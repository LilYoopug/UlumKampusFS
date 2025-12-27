<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for DiscussionPost store and update operations
 */
class DiscussionPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'faculty', 'student']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'thread_id' => $isUpdate ? ['nullable', 'integer', 'exists:discussion_threads,id'] : ['required', 'integer', 'exists:discussion_threads,id'],
            'content' => ['required', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:discussion_posts,id'],
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
            'thread_id' => 'Thread',
            'content' => 'Post content',
            'parent_id' => 'Parent post',
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
            'thread_id.exists' => 'The selected thread does not exist.',
            'parent_id.exists' => 'The selected parent post does not exist.',
            'content.required' => 'The post content is required.',
            'attachment_url.max' => 'The attachment URL may not exceed 500 characters.',
            'attachment_type.max' => 'The attachment type may not exceed 50 characters.',
        ];
    }
}