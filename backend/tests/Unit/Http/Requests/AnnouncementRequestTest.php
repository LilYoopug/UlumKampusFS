<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\AnnouncementRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnnouncementRequestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that valid data passes validation.
     */
    public function test_valid_announcement_data_passes_validation(): void
    {
        // Create related models first
        $faculty = \App\Models\Faculty::factory()->create();
        $course = \App\Models\Course::factory()->create(['faculty_id' => $faculty->id]);

        $data = [
            'title' => 'Important Announcement',
            'content' => 'This is the content of the announcement.',
            'category' => 'general',
            'target_audience' => 'everyone',
            'priority' => 'medium',
            'is_published' => true,
            'expires_at' => now()->addDays(7)->toDateString(),
            'allow_comments' => false,
            'attachment_url' => 'https://example.com/file.pdf',
            'attachment_type' => 'pdf',
            'order' => 1,
            'course_id' => $course->id,
            'faculty_id' => $faculty->id,
        ];

        $request = new AnnouncementRequest();
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

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->failed());
        $this->assertArrayHasKey('content', $validator->failed());
    }

    /**
     * Test that title with more than 255 characters fails validation.
     */
    public function test_title_exceeding_max_length_fails(): void
    {
        $data = [
            'title' => str_repeat('a', 256),
            'content' => 'Valid content',
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->failed());
    }

    /**
     * Test that invalid category enum value fails validation.
     */
    public function test_invalid_category_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'category' => 'invalid_category',
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('category', $validator->failed());
    }

    /**
     * Test that all valid category enum values pass validation.
     */
    public function test_all_valid_categories_pass_validation(): void
    {
        $validCategories = ['general', 'academic', 'event', 'emergency', 'policy', 'exam', 'holiday'];

        foreach ($validCategories as $category) {
            $data = [
                'title' => 'Test Announcement',
                'content' => 'Test content',
                'category' => $category,
            ];

            $request = new AnnouncementRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Category '{$category}' should pass validation");
        }
    }

    /**
     * Test that invalid target_audience enum value fails validation.
     */
    public function test_invalid_target_audience_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'target_audience' => 'invalid_audience',
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('target_audience', $validator->failed());
    }

    /**
     * Test that all valid target_audience enum values pass validation.
     */
    public function test_all_valid_target_audiences_pass_validation(): void
    {
        $validAudiences = ['everyone', 'students', 'faculty', 'staff', 'specific_course', 'specific_faculty'];

        foreach ($validAudiences as $audience) {
            $data = [
                'title' => 'Test Announcement',
                'content' => 'Test content',
                'target_audience' => $audience,
            ];

            $request = new AnnouncementRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Target audience '{$audience}' should pass validation");
        }
    }

    /**
     * Test that invalid priority enum value fails validation.
     */
    public function test_invalid_priority_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'priority' => 'invalid_priority',
        ];

        $request = new AnnouncementRequest();
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
                'title' => 'Test Announcement',
                'content' => 'Test content',
                'priority' => $priority,
            ];

            $request = new AnnouncementRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Priority '{$priority}' should pass validation");
        }
    }

    /**
     * Test that expires_at in the past fails validation.
     */
    public function test_expires_at_in_past_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'expires_at' => now()->subDay()->toDateString(),
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('expires_at', $validator->failed());
    }

    /**
     * Test that invalid URL for attachment_url fails validation.
     */
    public function test_invalid_attachment_url_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'attachment_url' => 'not-a-valid-url',
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('attachment_url', $validator->failed());
    }

    /**
     * Test that negative order value fails validation.
     */
    public function test_negative_order_fails_validation(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'order' => -1,
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('order', $validator->failed());
    }

    /**
     * Test that request is authorized.
     */
    public function test_request_is_authorized(): void
    {
        $request = new AnnouncementRequest();
        $this->assertTrue($request->authorize());
    }

    /**
     * Test that course_id must exist in courses table.
     */
    public function test_course_id_must_exist(): void
    {
        // Create a course that exists
        \App\Models\Faculty::factory()->create();
        \App\Models\Course::factory()->create();

        // Test with non-existent course_id
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'course_id' => 99999,
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('course_id', $validator->failed());
    }

    /**
     * Test that faculty_id must exist in faculties table.
     */
    public function test_faculty_id_must_exist(): void
    {
        // Create a faculty that exists
        \App\Models\Faculty::factory()->create();

        // Test with non-existent faculty_id
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'faculty_id' => 99999,
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('faculty_id', $validator->failed());
    }

    /**
     * Test that boolean fields accept boolean values.
     */
    public function test_boolean_fields_accept_boolean_values(): void
    {
        $data = [
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'is_published' => true,
            'allow_comments' => false,
        ];

        $request = new AnnouncementRequest();
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
            'title' => 'Test Announcement',
            'content' => 'Test content',
        ];

        $request = new AnnouncementRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }
}