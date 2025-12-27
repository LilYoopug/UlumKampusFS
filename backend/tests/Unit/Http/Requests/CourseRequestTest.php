<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\CourseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CourseRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid course data passes validation.
     */
    public function test_valid_course_data_passes_validation(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Introduction to Computer Science',
            'description' => 'A comprehensive introduction to programming.',
            'credit_hours' => 3,
            'capacity' => 50,
            'semester' => 'Fall',
            'year' => 2024,
            'schedule' => 'Mon/Wed 10:00-11:30',
            'room' => 'Science Building 101',
            'is_active' => true,
        ];

        $request = new CourseRequest();
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

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('faculty_id', $validator->failed());
        $this->assertArrayHasKey('major_id', $validator->failed());
        $this->assertArrayHasKey('instructor_id', $validator->failed());
        $this->assertArrayHasKey('code', $validator->failed());
        $this->assertArrayHasKey('name', $validator->failed());
    }

    /**
     * Test that code must be unique.
     */
    public function test_code_must_be_unique(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        // Create a course with code CS101
        \App\Models\Course::factory()->create([
            'code' => 'CS101',
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
        ]);

        // Try to create another course with same code
        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Another Course',
        ];

        $request = new CourseRequest();
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
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $course = \App\Models\Course::factory()->create([
            'code' => 'CS101',
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
        ]);

        // Simulate update request by setting method to PUT
        $request = new CourseRequest();
        $request->setMethod('PUT');

        // Get rules with the course ID for ignore
        $rules = $request->rules();

        // Manually replace the unique rule with one that ignores the course ID
        foreach ($rules['code'] as $key => $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\Unique) {
                $rules['code'][$key] = $rule->ignore($course->id);
            }
        }

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101', // Same code, but should pass for update
            'name' => 'Updated Course Name',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that faculty_id must exist in faculties table.
     */
    public function test_faculty_id_must_exist(): void
    {
        $data = [
            'faculty_id' => 99999,
            'major_id' => 1,
            'instructor_id' => 1,
            'code' => 'CS101',
            'name' => 'Test Course',
        ];

        $request = new CourseRequest();
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
        $faculty = \App\Models\Faculty::factory()->create();

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => 99999,
            'instructor_id' => 1,
            'code' => 'CS101',
            'name' => 'Test Course',
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('major_id', $validator->failed());
    }

    /**
     * Test that instructor_id must exist in users table.
     */
    public function test_instructor_id_must_exist(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => 99999,
            'code' => 'CS101',
            'name' => 'Test Course',
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('instructor_id', $validator->failed());
    }

    /**
     * Test that credit_hours must be a positive integer.
     */
    public function test_credit_hours_must_be_positive_integer(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Test Course',
            'credit_hours' => -1,
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('credit_hours', $validator->failed());
    }

    /**
     * Test that capacity must be a positive integer.
     */
    public function test_capacity_must_be_positive_integer(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Test Course',
            'capacity' => 0,
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('capacity', $validator->failed());
    }

    /**
     * Test that year must be a valid year.
     */
    public function test_year_must_be_valid(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Test Course',
            'year' => 1800, // Too old
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('year', $validator->failed());
    }

    /**
     * Test that semester must be a valid value.
     */
    public function test_semester_must_be_valid(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Test Course',
            'semester' => 'Winter', // Invalid semester value
        ];

        $request = new CourseRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('semester', $validator->failed());
    }

    /**
     * Test that all valid semester values pass validation.
     */
    public function test_all_valid_semesters_pass_validation(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $validSemesters = ['Fall', 'Spring', 'Summer'];

        foreach ($validSemesters as $semester) {
            $data = [
                'faculty_id' => $faculty->id,
                'major_id' => $major->id,
                'instructor_id' => $instructor->id,
                'code' => 'CS101',
                'name' => 'Test Course',
                'semester' => $semester,
            ];

            $request = new CourseRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes(), "Semester '{$semester}' should pass validation");
        }
    }

    /**
     * Test that is_active accepts boolean values.
     */
    public function test_is_active_accepts_boolean_values(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        foreach ([true, false] as $isActive) {
            $data = [
                'faculty_id' => $faculty->id,
                'major_id' => $major->id,
                'instructor_id' => $instructor->id,
                'code' => 'CS101',
                'name' => 'Test Course',
                'is_active' => $isActive,
            ];

            $request = new CourseRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertTrue($validator->passes());
        }
    }

    /**
     * Test that minimal valid data passes validation.
     */
    public function test_minimal_valid_data_passes_validation(): void
    {
        $faculty = \App\Models\Faculty::factory()->create();
        $major = \App\Models\Major::factory()->create(['faculty_id' => $faculty->id]);
        $instructor = \App\Models\User::factory()->create(['role' => 'faculty']);

        $data = [
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'instructor_id' => $instructor->id,
            'code' => 'CS101',
            'name' => 'Test Course',
        ];

        $request = new CourseRequest();
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
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        auth()->login($admin);

        $request = new CourseRequest();
        $this->assertTrue($request->authorize());

        auth()->logout();

        // Test as faculty
        $faculty = \App\Models\User::factory()->create(['role' => 'faculty']);
        auth()->login($faculty);

        $request = new CourseRequest();
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

        $request = new CourseRequest();
        $this->assertFalse($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for unauthenticated users.
     */
    public function test_request_is_not_authorized_for_unauthenticated_users(): void
    {
        auth()->logout();

        $request = new CourseRequest();
        $this->assertFalse($request->authorize());
    }
}