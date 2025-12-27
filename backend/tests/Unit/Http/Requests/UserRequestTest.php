<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UserRequest;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UserRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid user data passes validation for store operation.
     */
    public function test_valid_user_data_passes_validation_for_store(): void
    {
        $faculty = Faculty::factory()->create();
        $major = Major::factory()->create(['faculty_id' => $faculty->id]);

        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'student_id' => 'STU001',
            'gpa' => 3.5,
            'enrollment_year' => 2023,
            'graduation_year' => 2027,
            'phone' => '+1234567890',
            'address' => '123 Main St, City',
        ];

        $request = new UserRequest();
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

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->failed());
        $this->assertArrayHasKey('email', $validator->failed());
        $this->assertArrayHasKey('password', $validator->failed());
        $this->assertArrayHasKey('role', $validator->failed());
    }

    /**
     * Test that email must be unique.
     */
    public function test_email_must_be_unique(): void
    {
        // Create a user with an email
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->failed());
    }

    /**
     * Test that email can be ignored during update.
     */
    public function test_email_can_be_ignored_during_update(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        // Simulate update request by setting method to PUT
        $request = new UserRequest();
        $request->setMethod('PUT');

        // Get rules with the user ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the user ID
        foreach ($rules['email'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['email'][$key] = $rule->ignore($user->id);
            }
        }

        $data = [
            'name' => 'John Updated',
            'email' => 'john@example.com', // Same email, but should pass for update
            'role' => 'student',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that student_id must be unique.
     */
    public function test_student_id_must_be_unique(): void
    {
        // Create a user with a student_id
        User::factory()->create(['student_id' => 'STU001']);

        $data = [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'student_id' => 'STU001',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('student_id', $validator->failed());
    }

    /**
     * Test that student_id can be ignored during update.
     */
    public function test_student_id_can_be_ignored_during_update(): void
    {
        $user = User::factory()->create(['student_id' => 'STU001']);

        // Simulate update request by setting method to PUT
        $request = new UserRequest();
        $request->setMethod('PUT');

        // Get rules with the user ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the user ID
        foreach ($rules['student_id'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['student_id'][$key] = $rule->ignore($user->id);
            }
        }

        $data = [
            'name' => 'John Updated',
            'email' => 'john@example.com',
            'role' => 'student',
            'student_id' => 'STU001', // Same student_id, but should pass for update
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
            'name' => 'Test User',
            'email' => 'not-a-valid-email',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('email', $validator->failed());
    }

    /**
     * Test that password must be at least 8 characters.
     */
    public function test_password_must_be_at_least_8_characters(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->failed());
    }

    /**
     * Test that password must be confirmed.
     */
    public function test_password_must_be_confirmed(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'DifferentPassword',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('password', $validator->failed());
    }

    /**
     * Test that password is optional during update.
     */
    public function test_password_is_optional_during_update(): void
    {
        $faculty = Faculty::factory()->create();
        $major = Major::factory()->create(['faculty_id' => $faculty->id]);
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
        ]);

        // Simulate update request by setting method to PUT
        $request = new UserRequest();
        $request->setMethod('PUT');

        // Get rules with the user ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the user ID
        foreach ($rules['email'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['email'][$key] = $rule->ignore($user->id);
            }
        }

        $data = [
            'name' => 'John Updated',
            'email' => 'john@example.com',
            'role' => 'student',
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that invalid role enum value fails validation.
     */
    public function test_invalid_role_fails_validation(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'invalid_role',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('role', $validator->failed());
    }

    /**
     * Test that all valid role enum values pass validation.
     */
    public function test_all_valid_roles_pass_validation(): void
    {
        $validRoles = ['admin', 'faculty', 'student'];

        foreach ($validRoles as $role) {
            $data = [
                'name' => 'Test User',
                'email' => "test{$role}@example.com",
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'role' => $role,
            ];

            $request = new UserRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Role '{$role}' should pass validation");
        }
    }

    /**
     * Test that faculty_id must exist in faculties table.
     */
    public function test_faculty_id_must_exist(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'faculty_id' => 99999,
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('faculty_id', $validator->failed());
    }

    /**
     * Test that major_id must exist in majors table.
     */
    public function test_major_id_must_exist(): void
    {
        $faculty = Faculty::factory()->create();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'faculty_id' => $faculty->id,
            'major_id' => 99999,
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('major_id', $validator->failed());
    }

    /**
     * Test that GPA must be between 0 and 4.
     */
    public function test_gpa_must_be_between_0_and_4(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'gpa' => 5.0, // Invalid: greater than 4
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gpa', $validator->failed());

        // Test negative GPA
        $data['gpa'] = -1.0;
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('gpa', $validator->failed());
    }

    /**
     * Test that enrollment_year must be a valid year.
     */
    public function test_enrollment_year_must_be_valid(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'enrollment_year' => 1800, // Too old
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('enrollment_year', $validator->failed());
    }

    /**
     * Test that graduation_year must be a valid year.
     */
    public function test_graduation_year_must_be_valid(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'graduation_year' => 2200, // Too far in future
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('graduation_year', $validator->failed());
    }

    /**
     * Test that name exceeding max length fails validation.
     */
    public function test_name_exceeding_max_length_fails(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->failed());
    }

    /**
     * Test that minimal valid data passes validation.
     */
    public function test_minimal_valid_data_passes_validation(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that request is authorized for admins and faculty.
     */
    public function test_request_is_authorized_for_admins_and_faculty(): void
    {
        // Test as admin
        $admin = User::factory()->create(['role' => 'admin']);
        auth()->login($admin);

        $request = new UserRequest();
        $this->assertTrue($request->authorize());

        auth()->logout();

        // Test as faculty
        $facultyUser = User::factory()->create(['role' => 'faculty']);
        auth()->login($facultyUser);

        $request = new UserRequest();
        $this->assertTrue($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for students.
     */
    public function test_request_is_not_authorized_for_students(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        auth()->login($student);

        $request = new UserRequest();
        $this->assertFalse($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for unauthenticated users.
     */
    public function test_request_is_not_authorized_for_unauthenticated_users(): void
    {
        auth()->logout();

        $request = new UserRequest();
        $this->assertFalse($request->authorize());
    }

    /**
     * Test that student_id max length is respected.
     */
    public function test_student_id_max_length_fails_when_exceeded(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'student_id' => str_repeat('a', 51),
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('student_id', $validator->failed());
    }

    /**
     * Test that phone max length is respected.
     */
    public function test_phone_max_length_fails_when_exceeded(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'phone' => str_repeat('a', 51),
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('phone', $validator->failed());
    }

    /**
     * Test that address max length is respected.
     */
    public function test_address_max_length_fails_when_exceeded(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'student',
            'address' => str_repeat('a', 501),
        ];

        $request = new UserRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('address', $validator->failed());
    }
}