<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\GradeRequest;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GradeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $course;
    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        $instructor = User::factory()->create(['role' => 'faculty']);
        $this->course = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $instructor->id,
        ]);

        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $instructor->id,
        ]);

        // Enroll the student in the course
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
    }

    /**
     * Test that valid grade data passes validation.
     */
    public function test_valid_grade_data_passes_validation(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $this->assignment->id,
            'grade' => 85.5,
            'comments' => 'Good work on this assignment.',
        ];

        $request = new GradeRequest();
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

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('user_id', $validator->failed());
        $this->assertArrayHasKey('course_id', $validator->failed());
        $this->assertArrayHasKey('grade', $validator->failed());
    }

    /**
     * Test that grade must be numeric.
     */
    public function test_grade_must_be_numeric(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 'not_a_number',
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('grade', $validator->failed());
    }

    /**
     * Test that grade must be at least 0.
     */
    public function test_grade_must_be_at_least_0(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => -5,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('grade', $validator->failed());
    }

    /**
     * Test that grade must not exceed 100.
     */
    public function test_grade_must_not_exceed_100(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 105,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('grade', $validator->failed());
    }

    /**
     * Test that grade_letter must be one of valid values.
     */
    public function test_grade_letter_must_be_valid(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'X',
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('grade_letter', $validator->failed());
    }

    /**
     * Test that user_id must exist in users table.
     */
    public function test_user_id_must_exist(): void
    {
        $data = [
            'user_id' => 99999,
            'course_id' => $this->course->id,
            'grade' => 85,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('user_id', $validator->failed());
    }

    /**
     * Test that course_id must exist in courses table.
     */
    public function test_course_id_must_exist(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => 99999,
            'grade' => 85,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('course_id', $validator->failed());
    }

    /**
     * Test that assignment_id must exist in assignments table.
     */
    public function test_assignment_id_must_exist(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => 99999,
            'grade' => 85,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('assignment_id', $validator->failed());
    }

    /**
     * Test that student must be enrolled in the course.
     */
    public function test_student_must_be_enrolled_in_course(): void
    {
        // Create a student not enrolled in the course
        $unenrolledStudent = User::factory()->create(['role' => 'student']);

        $data = [
            'user_id' => $unenrolledStudent->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('user_id', $validator->errors()->toArray());
    }

    /**
     * Test that assignment must belong to the course.
     */
    public function test_assignment_must_belong_to_course(): void
    {
        // Create an assignment for a different course
        $otherCourse = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);

        $otherAssignment = Assignment::factory()->create([
            'course_id' => $otherCourse->id,
            'created_by' => $this->faculty->id,
        ]);

        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $otherAssignment->id,
            'grade' => 85,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('assignment_id', $validator->errors()->toArray());
    }

    /**
     * Test that assignment_id is nullable.
     */
    public function test_assignment_id_is_nullable(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'assignment_id' => null,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that comments is nullable.
     */
    public function test_comments_is_nullable(): void
    {
        $data = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'comments' => null,
        ];

        $request = new GradeRequest();
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that grade letter is auto-calculated from grade.
     */
    public function test_grade_letter_is_auto_calculated(): void
    {
        $request = new GradeRequest();
        $request->merge([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
        ]);

        $request->prepareForValidation();

        $this->assertEquals('A', $request->input('grade_letter'));
    }

    /**
     * Test all grade letter thresholds.
     */
    public function test_all_grade_letter_thresholds(): void
    {
        $cases = [
            95 => 'A',
            90 => 'A',
            89 => 'B',
            85 => 'B',
            80 => 'B',
            79 => 'C',
            75 => 'C',
            70 => 'C',
            69 => 'D',
            65 => 'D',
            60 => 'D',
            59 => 'F',
            0 => 'F',
        ];

        foreach ($cases as $grade => $expectedLetter) {
            $request = new GradeRequest();
            $request->merge([
                'user_id' => $this->student->id,
                'course_id' => $this->course->id,
                'grade' => $grade,
            ]);

            $request->prepareForValidation();

            $this->assertEquals($expectedLetter, $request->input('grade_letter'),
                "Grade {$grade} should calculate to {$expectedLetter}");
        }
    }

    /**
     * Test boundary grade values.
     */
    public function test_boundary_grade_values(): void
    {
        $boundaryCases = [
            ['grade' => 0, 'should_pass' => true],
            ['grade' => 100, 'should_pass' => true],
            ['grade' => 50, 'should_pass' => true],
            ['grade' => 99.99, 'should_pass' => true],
        ];

        foreach ($boundaryCases as $case) {
            $data = [
                'user_id' => $this->student->id,
                'course_id' => $this->course->id,
                'grade' => $case['grade'],
            ];

            $request = new GradeRequest();
            $rules = $request->rules();
            $validator = Validator::make($data, $rules);

            $this->assertEquals($case['should_pass'], $validator->passes(),
                "Grade {$case['grade']} should " . ($case['should_pass'] ? 'pass' : 'fail'));
        }
    }

    /**
     * Test that request is authorized for admins and faculty.
     */
    public function test_request_is_authorized_for_admins_and_faculty(): void
    {
        // Test as admin
        auth()->login($this->admin);
        $request = new GradeRequest();
        $this->assertTrue($request->authorize());
        auth()->logout();

        // Test as faculty
        auth()->login($this->faculty);
        $request = new GradeRequest();
        $this->assertTrue($request->authorize());
        auth()->logout();
    }

    /**
     * Test that request is not authorized for students.
     */
    public function test_request_is_not_authorized_for_students(): void
    {
        auth()->login($this->student);

        $request = new GradeRequest();
        $this->assertFalse($request->authorize());

        auth()->logout();
    }

    /**
     * Test that request is not authorized for unauthenticated users.
     */
    public function test_request_is_not_authorized_for_unauthenticated_users(): void
    {
        auth()->logout();

        $request = new GradeRequest();
        $this->assertFalse($request->authorize());
    }
}