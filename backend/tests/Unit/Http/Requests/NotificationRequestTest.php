<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\NotificationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class NotificationRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid notification data passes validation for store operation.
     */
    public function test_valid_notification_data_passes_validation_for_store(): void
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'type' => 'assignment_due',
            'title' => 'Assignment Due Reminder',
            'message' => 'Your assignment is due tomorrow.',
            'priority' => 'high',
            'action_url' => 'https://example.com/assignments/1',
            'related_entity_type' => 'App\\Models\\Assignment',
            'related_entity_id' => 1,
            'expires_at' => now()->addDays(7)->toDateString(),
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that required fields fail validation when missing.
     */
    public function test_required_fields_fail_when_missing(): void
    {
        $data = [];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('user_id', $validator->failed());
        $this->assertArrayHasKey('type', $validator->failed());
        $this->assertArrayHasKey('title', $validator->failed());
        $this->assertArrayHasKey('message', $validator->failed());
    }

    /**
     * Test that user_id must exist in users table.
     */
    public function test_user_id_must_exist(): void
    {
        $data = [
            'user_id' => 99999,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('user_id', $validator->failed());
    }

    /**
     * Test that title exceeding max length fails validation.
     */
    public function test_title_exceeding_max_length_fails(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => str_repeat('a', 256),
            'message' => 'Test message',
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->failed());
    }

    /**
     * Test that invalid priority enum value fails validation.
     */
    public function test_invalid_priority_fails_validation(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'priority' => 'invalid_priority',
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('priority', $validator->failed());
    }

    /**
     * Test that all valid priority enum values pass validation.
     */
    public function test_all_valid_priorities_pass_validation(): void
    {
        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($validPriorities as $priority) {
            $data = [
                'user_id' => 1,
                'type' => 'test',
                'title' => 'Test',
                'message' => 'Test message',
                'priority' => $priority,
            ];

            $request = new NotificationRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Priority '{$priority}' should pass validation");
        }
    }

    /**
     * Test that invalid URL for action_url fails validation.
     */
    public function test_invalid_action_url_fails_validation(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'action_url' => 'not-a-valid-url',
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('action_url', $validator->failed());
    }

    /**
     * Test that expires_at in the past fails validation.
     */
    public function test_expires_at_in_past_fails_validation(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'expires_at' => now()->subDay()->toDateString(),
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('expires_at', $validator->failed());
    }

    /**
     * Test that read_at can be nullable.
     */
    public function test_read_at_can_be_nullable(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'read_at' => null,
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that is_read accepts boolean values.
     */
    public function test_is_read_accepts_boolean_values(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'is_read' => true,
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that minimal valid data passes validation.
     */
    public function test_minimal_valid_data_passes_validation(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that user_id is nullable during update.
     */
    public function test_user_id_is_nullable_during_update(): void
    {
        // Simulate update request by setting method to PUT
        $request = new NotificationRequest();
        $request->setMethod('PUT');

        $rules = $request->rules();

        $data = [
            'title' => 'Updated Title',
            'message' => 'Updated message',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that type is nullable during update.
     */
    public function test_type_is_nullable_during_update(): void
    {
        // Simulate update request by setting method to PUT
        $request = new NotificationRequest();
        $request->setMethod('PUT');

        $rules = $request->rules();

        $data = [
            'user_id' => 1,
            'title' => 'Updated Title',
            'message' => 'Updated message',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that sent_at can be nullable.
     */
    public function test_sent_at_can_be_nullable(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'sent_at' => null,
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that is_sent accepts boolean values.
     */
    public function test_is_sent_accepts_boolean_values(): void
    {
        $data = [
            'user_id' => 1,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
            'is_sent' => true,
        ];

        $request = new NotificationRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }
}