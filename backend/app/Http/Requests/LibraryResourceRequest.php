<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request validation for LibraryResource store and update operations
 */
class LibraryResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admins and Faculty can create/update library resources
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

        return [
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'faculty_id' => ['nullable', 'integer', 'exists:faculties,id'],
            'created_by' => $isUpdate ? ['nullable', 'integer', 'exists:users,id'] : ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'resource_type' => ['nullable', 'string', 'in:document,video,audio,link,book,article,journal,other'],
            'access_level' => ['nullable', 'string', 'in:public,faculty,course'],
            'file_url' => ['nullable', 'url', 'max:500'],
            'file_type' => ['nullable', 'string', 'max:50'],
            'file_size' => ['nullable', 'integer', 'min:0'],
            'external_link' => ['nullable', 'url', 'max:500'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50'],
            'doi' => ['nullable', 'string', 'max:100'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:2100'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
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
            'faculty_id' => 'Faculty',
            'created_by' => 'Creator',
            'title' => 'Resource title',
            'description' => 'Description',
            'resource_type' => 'Resource type',
            'access_level' => 'Access level',
            'file_url' => 'File URL',
            'file_type' => 'File type',
            'file_size' => 'File size',
            'external_link' => 'External link',
            'author' => 'Author',
            'publisher' => 'Publisher',
            'isbn' => 'ISBN',
            'doi' => 'DOI',
            'publication_year' => 'Publication year',
            'tags' => 'Tags',
            'is_published' => 'Published status',
            'order' => 'Display order',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'course_id.exists' => 'The selected course does not exist.',
            'faculty_id.exists' => 'The selected faculty does not exist.',
            'created_by.exists' => 'The selected user does not exist.',
            'title.required' => 'The resource title is required.',
            'title.max' => 'The resource title may not exceed 255 characters.',
            'resource_type.in' => 'The resource type must be one of: document, video, audio, link, book, article, journal, or other.',
            'access_level.in' => 'The access level must be one of: public, faculty, or course.',
            'file_url.url' => 'The file URL must be a valid URL.',
            'file_url.max' => 'The file URL may not exceed 500 characters.',
            'external_link.url' => 'The external link must be a valid URL.',
            'external_link.max' => 'The external link may not exceed 500 characters.',
            'publication_year.min' => 'The publication year must be at least 1000.',
            'publication_year.max' => 'The publication year may not exceed 2100.',
            'file_size.min' => 'The file size must be at least 0.',
            'order.min' => 'The order value must be at least 0.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-set created_by if not provided during create
        if (!$this->isMethod('PUT') && !$this->isMethod('PATCH') && !$this->has('created_by')) {
            $this->merge([
                'created_by' => auth()->id(),
            ]);
        }

        // Set published_at when is_published is true
        if ($this->has('is_published') && $this->is_published) {
            $this->merge([
                'published_at' => now(),
            ]);
        }
    }
}