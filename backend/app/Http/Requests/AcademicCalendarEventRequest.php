<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * AcademicCalendarEventRequest
 *
 * Validates requests for academic calendar event CRUD operations.
 * Ensures data integrity for calendar events including:
 * - Required fields (title, start_date, end_date, category)
 * - Date validation (end_date must be after start_date)
 * - Category validation (must be one of predefined categories)
 */
class AcademicCalendarEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Admin and Faculty users can create/update calendar events.
     * All authenticated users can view events.
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
        $categories = array_keys(\App\Models\AcademicCalendarEvent::getCategories());

        return [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'required|string|in:' . implode(',', $categories),
            'description' => 'nullable|string|max:2000',
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
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title must not exceed 255 characters.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'category.required' => 'Event category is required.',
            'category.in' => 'Invalid category selected.',
            'description.max' => 'Description must not exceed 2000 characters.',
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
            'title' => 'Event Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'category' => 'Category',
            'description' => 'Description',
        ];
    }
}