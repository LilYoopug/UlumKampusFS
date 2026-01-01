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
        // Admins, Faculty, Dosen, Prodi Admin, and Super Admin can create/update library resources
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'faculty', 'dosen', 'prodi_admin', 'super_admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        // For updates, fields that come from the form are validated
        // Fields not in the form should be excluded from validation
        $editableFields = ['title', 'author', 'publication_year', 'year', 'type', 'description', 'cover_url', 'source_type', 'file_url', 'external_link', 'source_url'];

        // Build validation rules based on what's actually submitted
        $rules = [];

        // Core required fields (always validate these)
        if ($this->has('title') || !$isUpdate) {
            $rules['title'] = ['required', 'string', 'max:255'];
        }

        // Optional fields from edit form
        if ($this->has('author')) {
            $rules['author'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->has('publication_year') || $this->has('year')) {
            $rules['publication_year'] = ['nullable', 'integer', 'min:1000', 'max:2100'];
            $rules['year'] = ['nullable', 'integer', 'min:1000', 'max:2100'];
        }

        if ($this->has('type')) {
            $rules['type'] = ['nullable', 'string', 'in:document,video,audio,link,book,article,journal,other'];
        }

        if ($this->has('description')) {
            $rules['description'] = ['nullable', 'string'];
        }

        if ($this->has('cover_url')) {
            $rules['cover_url'] = ['nullable', 'url', 'max:500'];
        }

        // These fields should always be validated since they're commonly used
        // and may be mapped from camelCase by prepareForValidation
        $rules['source_type'] = ['nullable', 'string', 'in:upload,link,embed'];
        $rules['file_url'] = ['nullable', 'string', 'max:500'];
        $rules['external_link'] = ['nullable', 'string', 'max:500'];
        $rules['source_url'] = ['nullable', 'string', 'max:500'];
        $rules['cover_url'] = ['nullable', 'string', 'max:500'];

        // Less commonly edited fields (only validate if present)
        if ($this->has('course_id')) {
            $rules['course_id'] = ['nullable', 'string', 'exists:courses,id'];
        }

        if ($this->has('faculty_id')) {
            $rules['faculty_id'] = ['nullable', 'string', 'exists:faculties,id'];
        }

        if ($this->has('created_by')) {
            $rules['created_by'] = ['nullable', 'integer', 'exists:users,id'];
        }

        if ($this->has('access_level')) {
            $rules['access_level'] = ['nullable', 'string', 'in:public,students,faculty,specific_course,specific_faculty'];
        }

        if ($this->has('file_type')) {
            $rules['file_type'] = ['nullable', 'string', 'max:50'];
        }

        if ($this->has('file_size')) {
            $rules['file_size'] = ['nullable', 'integer', 'min:0'];
        }

        if ($this->has('publisher')) {
            $rules['publisher'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->has('isbn')) {
            $rules['isbn'] = ['nullable', 'string', 'max:50'];
        }

        if ($this->has('doi')) {
            $rules['doi'] = ['nullable', 'string', 'max:100'];
        }

        if ($this->has('tags')) {
            $rules['tags'] = ['nullable', 'string', 'max:500'];
        }

        if ($this->has('is_published')) {
            $rules['is_published'] = ['nullable', 'boolean'];
        }

        if ($this->has('order')) {
            $rules['order'] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
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
            'type' => 'Resource type',
            'access_level' => 'Access level',
            'file_url' => 'File URL',
            'file_type' => 'File type',
            'file_size' => 'File size',
            'external_link' => 'External link',
            'cover_url' => 'Cover URL',
            'source_type' => 'Source type',
            'source_url' => 'Source URL',
            'author' => 'Author',
            'publisher' => 'Publisher',
            'isbn' => 'ISBN',
            'doi' => 'DOI',
            'publication_year' => 'Publication year',
            'year' => 'Publication year',
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
            'type.in' => 'The resource type must be one of: document, video, audio, link, book, article, journal, or other.',
            'access_level.in' => 'The access level must be one of: public, students, faculty, specific_course, or specific_faculty.',
            'file_url.url' => 'The file URL must be a valid URL.',
            'file_url.max' => 'The file URL may not exceed 500 characters.',
            'external_link.url' => 'The external link must be a valid URL.',
            'external_link.max' => 'The external link may not exceed 500 characters.',
            'cover_url.url' => 'The cover URL must be a valid URL.',
            'cover_url.max' => 'The cover URL may not exceed 500 characters.',
            'source_type.in' => 'The source type must be one of: upload, link, or embed.',
            'source_url.max' => 'The source URL may not exceed 500 characters.',
            'publication_year.min' => 'The publication year must be at least 1000.',
            'publication_year.max' => 'The publication year may not exceed 2100.',
            'year.min' => 'The publication year must be at least 1000.',
            'year.max' => 'The publication year may not exceed 2100.',
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

        // Handle field name differences between frontend and backend
        // Map 'year' to 'publication_year'
        if ($this->has('year') && !$this->has('publication_year')) {
            $this->merge([
                'publication_year' => $this->input('year'),
            ]);
        }

        // Map 'coverUrl' to 'cover_url'
        if ($this->has('coverUrl') && !$this->has('cover_url')) {
            $this->merge([
                'cover_url' => $this->input('coverUrl'),
            ]);
        }

        // Map 'sourceType' to 'source_type'
        if ($this->has('sourceType') && !$this->has('source_type')) {
            $this->merge([
                'source_type' => $this->input('sourceType'),
            ]);
        }

        // Map frontend camelCase to backend snake_case
        if ($this->has('fileUrl') && !$this->has('file_url')) {
            $this->merge(['file_url' => $this->input('fileUrl')]);
        }
        
        if ($this->has('sourceUrl') && !$this->has('source_url')) {
            $this->merge(['source_url' => $this->input('sourceUrl')]);
        }

        // Set published_at when is_published is true
        if ($this->has('is_published') && $this->is_published) {
            $this->merge([
                'published_at' => now(),
            ]);
        }
    }
}
