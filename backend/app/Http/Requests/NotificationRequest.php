<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for Notification store and update operations
 */
class NotificationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'user_id' => $isUpdate ? ['nullable', 'integer', 'exists:users,id'] : ['required', 'integer', 'exists:users,id'],
            'type' => $isUpdate ? ['nullable', 'string', 'max:100'] : ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'is_read' => ['nullable', 'boolean'],
            'read_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'action_url' => ['nullable', 'url'],
            'related_entity_type' => ['nullable', 'string', 'max:255'],
            'related_entity_id' => ['nullable', 'integer'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_sent' => ['nullable', 'boolean'],
            'sent_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'User',
            'type' => 'Notification type',
            'title' => 'Notification title',
            'message' => 'Notification message',
            'is_read' => 'Read status',
            'read_at' => 'Read at',
            'priority' => 'Priority',
            'action_url' => 'Action URL',
            'related_entity_type' => 'Related entity type',
            'related_entity_id' => 'Related entity ID',
            'expires_at' => 'Expiration date',
            'is_sent' => 'Sent status',
            'sent_at' => 'Sent at',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'type.required' => 'The notification type is required.',
            'type.max' => 'The notification type may not exceed 100 characters.',
            'title.required' => 'The notification title is required.',
            'title.max' => 'The notification title may not exceed 255 characters.',
            'message.required' => 'The notification message is required.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'action_url.url' => 'The action URL must be a valid URL.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }
}