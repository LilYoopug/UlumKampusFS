<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\FacultyRequest;
use App\Models\Faculty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FacultyRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid faculty data passes validation for store operation.
     */
    public function test_valid_faculty_data_passes_validation_for_store(): void
    {
        $data = [
            'name' => 'Faculty of Science',
            'code' => 'SCI',
            'description' => 'A leading science faculty',
            'dean_name' => 'Dr. John Smith',
            'email' => 'dean.science@university.edu',
            'phone' => '+1234567890',
            'is_active' => true,
        ];

        $request = new FacultyRequest();
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

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->failed());
        $this->assertArrayHasKey('code', $validator->failed());
    }

    /**
     * Test that code must be unique.
     */
    public function test_code_must_be_unique(): void
    {
        // Create a faculty with a code
        Faculty::factory()->create(['code' => 'ENG']);

        $data = [
            'name' => 'Engineering Faculty',
            'code' => 'ENG',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('code', $validator->failed());
    }

    /**
     * Test that code can be ignored during update.
     */
    public function test_code_can_be_ignored_during_update(): void
    {
        $faculty = Faculty::factory()->create(['code' => 'SCI']);

        // Simulate update request by setting method to PUT
        $request = new FacultyRequest();
        $request->setMethod('PUT');

        // Get rules with the faculty ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the faculty ID
        foreach ($rules['code'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['code'][$key] = $rule->ignore($faculty->id);
            }
        }

        $data = [
            'name' => 'Faculty of Science Updated',
            'code' => 'SCI', // Same code, but should pass for update
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that invalid email format fails validation.
     */
    public function test_invalid_email_format_fails_validation(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'email' => 'not-a-valid-email',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->failed());
    }

    /**
     * Test that valid email format passes validation.
     */
    public function test_valid_email_format_passes_validation(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'email' => 'valid.email@university.edu',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that name exceeding max length fails validation.
     */
    public function test_name_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'code' => 'TST',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->failed());
    }

    /**
     * Test that code exceeding max length fails validation.
     */
    public function test_code_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => str_repeat('a', 51),
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('code', $validator->failed());
    }

    /**
     * Test that dean_name exceeding max length fails validation.
     */
    public function test_dean_name_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'dean_name' => str_repeat('a', 256),
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('dean_name', $validator->failed());
    }

    /**
     * Test that email exceeding max length fails validation.
     */
    public function test_email_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'email' => str_repeat('a', 250) . '@university.edu',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->failed());
    }

    /**
     * Test that phone exceeding max length fails validation.
     */
    public function test_phone_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'phone' => str_repeat('a', 51),
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->failed());
    }

    /**
     * Test that description is nullable.
     */
    public function test_description_is_nullable(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'description' => null,
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that dean_name is nullable.
     */
    public function test_dean_name_is_nullable(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'dean_name' => null,
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that email is nullable.
     */
    public function test_email_is_nullable(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'email' => null,
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that phone is nullable.
     */
    public function test_phone_is_nullable(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'phone' => null,
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that is_active accepts boolean true.
     */
    public function test_is_active_accepts_boolean_true(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'is_active' => true,
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that is_active accepts boolean false.
     */
    public function test_is_active_accepts_boolean_false(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'is_active' => false,
        ];

        $request = new FacultyRequest();
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
            'name' => 'Test Faculty',
            'code' => 'TST',
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that description can be a long string.
     */
    public function test_description_can_be_long_string(): void
    {
        $data = [
            'name' => 'Test Faculty',
            'code' => 'TST',
            'description' => str_repeat('a', 1000), // text field, no max length
        ];

        $request = new FacultyRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that custom attributes are returned correctly.
     */
    public function test_custom_attributes_are_returned_correctly(): void
    {
        $request = new FacultyRequest();
        $attributes = $request->attributes();

        $this->assertEquals('Faculty name', $attributes['name']);
        $this->assertEquals('Faculty code', $attributes['code']);
        $this->assertEquals('Description', $attributes['description']);
        $this->assertEquals('Dean name', $attributes['dean_name']);
        $this->assertEquals('Email address', $attributes['email']);
        $this->assertEquals('Phone number', $attributes['phone']);
        $this->assertEquals('Active status', $attributes['is_active']);
    }

    /**
     * Test that custom error messages are returned correctly.
     */
    public function test_custom_error_messages_are_returned_correctly(): void
    {
        $request = new FacultyRequest();
        $messages = $request->messages();

        $this->assertEquals('Faculty name is required.', $messages['name.required']);
        $this->assertEquals('Faculty code is required.', $messages['code.required']);
        $this->assertEquals('A faculty with this code already exists.', $messages['code.unique']);
        $this->assertEquals('Please provide a valid email address.', $messages['email.email']);
        $this->assertEquals('Active status must be true or false.', $messages['is_active.boolean']);
    }

    /**
     * Test that request is authorized for admins and faculty.
     */
    public function test_request_is_authorized_for_admins_and_faculty(): void
    {
        // Test as admin
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        auth()->login($admin);

        $request = new FacultyRequest();
        $this->assertTrue($request->authorize());

        auth()->logout();

        // Test as faculty
        $facultyUser = \App\Models\User::factory()->create(['role' => 'faculty']);
        auth()->login($facultyUser);

        $request = new FacultyRequest();
        $this->assertTrue($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for students.
     */
    public function test_request_is_not_authorized_for_students(): void
    {
        $student = \App\Models\User::factory()->create(['role' => 'student']);
        auth()->login($student);

        $request = new FacultyRequest();
        $this->assertFalse($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for unauthenticated users.
     */
    public function test_request_is_not_authorized_for_unauthenticated_users(): void
    {
        auth()->logout();

        $request = new FacultyRequest();
        $this->assertFalse($request->authorize());
    }

    /**
     * Test all fields can be updated in update mode.
     */
    public function test_all_fields_can_be_updated_in_update_mode(): void
    {
        $faculty = Faculty::factory()->create();

        // Simulate update request by setting method to PUT
        $request = new FacultyRequest();
        $request->setMethod('PUT');

        // Get rules with the faculty ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the faculty ID
        foreach ($rules['code'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['code'][$key] = $rule->ignore($faculty->id);
            }
        }

        $data = [
            'name' => 'Updated Faculty Name',
            'code' => $faculty->code,
            'description' => 'Updated description',
            'dean_name' => 'Dr. Updated',
            'email' => 'updated@university.edu',
            'phone' => '+9876543210',
            'is_active' => false,
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that PATCH method works the same as PUT for update.
     */
    public function test_patch_method_works_the_same_as_put_for_update(): void
    {
        $faculty = Faculty::factory()->create();

        // Simulate update request by setting method to PATCH
        $request = new FacultyRequest();
        $request->setMethod('PATCH');

        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the faculty ID
        foreach ($rules['code'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['code'][$key] = $rule->ignore($faculty->id);
            }
        }

        $data = [
            'name' => 'Updated via PATCH',
            'code' => $faculty->code,
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }
}