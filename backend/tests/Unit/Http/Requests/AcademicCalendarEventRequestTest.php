<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\AcademicCalendarEventRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AcademicCalendarEventRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid academic calendar event data passes validation.
     */
    public function test_valid_event_data_passes_validation(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-25',
            'category' => 'exam',
            'description' => 'Final examination period for all courses.',
        ];

        $request = new AcademicCalendarEventRequest();
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

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->failed());
        $this->assertArrayHasKey('start_date', $validator->failed());
        $this->assertArrayHasKey('end_date', $validator->failed());
        $this->assertArrayHasKey('category', $validator->failed());
    }

    /**
     * Test that title must be a string.
     */
    public function test_title_must_be_string(): void
    {
        $data = [
            'title' => 12345,
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-25',
            'category' => 'exam',
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->failed());
    }

    /**
     * Test that start_date must be a valid date.
     */
    public function test_start_date_must_be_valid_date(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => 'not-a-date',
            'end_date' => '2025-12-25',
            'category' => 'exam',
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_date', $validator->failed());
    }

    /**
     * Test that end_date must be a valid date.
     */
    public function test_end_date_must_be_valid_date(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => '2025-12-20',
            'end_date' => 'not-a-date',
            'category' => 'exam',
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('end_date', $validator->failed());
    }

    /**
     * Test that category must be a string.
     */
    public function test_category_must_be_string(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-25',
            'category' => 123,
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('category', $validator->failed());
    }

    /**
     * Test that description is nullable.
     */
    public function test_description_is_nullable(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-25',
            'category' => 'exam',
            'description' => null,
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that description can be omitted.
     */
    public function test_description_can_be_omitted(): void
    {
        $data = [
            'title' => 'Final Examinations',
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-25',
            'category' => 'exam',
        ];

        $request = new AcademicCalendarEventRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that request is authorized.
     */
    public function test_request_is_authorized(): void
    {
        $request = new AcademicCalendarEventRequest();
        $this->assertTrue($request->authorize());
    }

    /**
     * Test custom error messages exist.
     */
    public function test_custom_error_messages_exist(): void
    {
        $request = new AcademicCalendarEventRequest();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('title.required', $messages);
        $this->assertArrayHasKey('start_date.required', $messages);
        $this->assertArrayHasKey('end_date.required', $messages);
        $this->assertArrayHasKey('category.required', $messages);
    }

    /**
     * Test custom attributes exist.
     */
    public function test_custom_attributes_exist(): void
    {
        $request = new AcademicCalendarEventRequest();
        $attributes = $request->attributes();

        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('title', $attributes);
        $this->assertArrayHasKey('start_date', $attributes);
        $this->assertArrayHasKey('end_date', $attributes);
        $this->assertArrayHasKey('category', $attributes);
        $this->assertArrayHasKey('description', $attributes);
    }
}