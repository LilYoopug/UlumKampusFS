<?php

namespace Tests\Feature\Api;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $admin;
    protected User $faculty;
    protected Course $course1;
    protected Course $course2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create(['role' => 'student']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);

        $facultyModel = Faculty::factory()->create();
        $majorModel = Major::factory()->create(['faculty_id' => $facultyModel->id]);

        $this->course1 = Course::factory()->create([
            'faculty_id' => $facultyModel->id,
            'major_id' => $majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);

        $this->course2 = Course::factory()->create([
            'faculty_id' => $facultyModel->id,
            'major_id' => $majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // MY COURSES Tests
    // -------------------------------------------------------------------------

    public function test_my_courses_returns_enrolled_courses_for_student(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course2->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_my_courses_excludes_dropped_courses(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course2->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_my_courses_returns_empty_for_new_student(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEmpty($data);
    }

    public function test_my_courses_includes_course_details(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('faculty', $data[0]);
        $this->assertArrayHasKey('major', $data[0]);
        $this->assertArrayHasKey('instructor', $data[0]);
    }

    public function test_my_courses_requires_student_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MY ASSIGNMENTS Tests
    // -------------------------------------------------------------------------

    public function test_my_assignments_returns_assignments_for_enrolled_courses(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $module = CourseModule::factory()->create(['course_id' => $this->course1->id]);
        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
            'title' => 'Assignment 1',
        ]);

        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
            'title' => 'Assignment 2',
        ]);

        // Unpublished assignment should not appear
        Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'is_published' => false,
        ]);

        // Assignment from non-enrolled course should not appear
        Assignment::factory()->create([
            'course_id' => $this->course2->id,
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_my_assignments_includes_submission_status(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $module = CourseModule::factory()->create(['course_id' => $this->course1->id]);
        $assignment1 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
        ]);

        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
        ]);

        // Create submission for assignment1
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment1->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Find assignment1 and verify status
        $submittedAssignment = collect($data)->firstWhere('id', $assignment1->id);
        $this->assertEquals('submitted', $submittedAssignment['submission_status']);
        $this->assertEquals(85, $submittedAssignment['my_grade']);

        // Find assignment2 and verify status
        $notSubmittedAssignment = collect($data)->firstWhere('id', $assignment2->id);
        $this->assertEquals('not_submitted', $notSubmittedAssignment['submission_status']);
        $this->assertNull($notSubmittedAssignment['my_grade']);
    }

    public function test_my_assignments_returns_empty_with_no_enrollments(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEmpty($data);
    }

    public function test_my_assignments_requires_student_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(403);
    }

    public function test_my_assignments_includes_latest_attempt_info(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $module = CourseModule::factory()->create(['course_id' => $this->course1->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
        ]);

        // Create multiple submissions
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'attempt_number' => 1,
            'grade' => 70,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'attempt_number' => 2,
            'grade' => 90,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(200);

        $data = $response->json('data');
        $latestAssignment = collect($data)->firstWhere('id', $assignment->id);

        // Should show latest attempt grade
        $this->assertEquals(90, $latestAssignment['my_grade']);
    }

    // -------------------------------------------------------------------------
    // MY GRADES Tests
    // -------------------------------------------------------------------------

    public function test_my_grades_returns_student_grades(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 85,
            'max_score' => 100,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 92,
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data['grades']);
    }

    public function test_my_grades_calculates_gpa_correctly(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        // Create grades with different letter grades
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 95, // A = 4.0
            'max_score' => 100,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 85, // B = 3.0
            'max_score' => 100,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 75, // C = 2.0
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200);

        $data = $response->json('data');
        // GPA = (4.0 + 3.0 + 2.0) / 3 = 3.0
        $this->assertEquals(3.0, $data['gpa']);
        $this->assertEquals(3, $data['total_courses']); // Only unique courses
    }

    public function test_my_grades_calculates_gpa_for_same_course_as_one(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        // Multiple grades from same course (should count as 1 for total courses)
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 90, // A = 4.0
            'max_score' => 100,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 85, // B = 3.0
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200);

        $data = $response->json('data');
        // GPA = (4.0 + 3.0) / 2 = 3.5
        $this->assertEquals(3.5, $data['gpa']);
        $this->assertEquals(1, $data['total_courses']); // Same course, counted once
    }

    public function test_my_grades_returns_zero_gpa_for_no_grades(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(0.0, $data['gpa']);
        $this->assertEquals(0, $data['total_courses']);
        $this->assertEmpty($data['grades']);
    }

    public function test_my_grades_includes_course_and_assignment_details(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $module = CourseModule::factory()->create(['course_id' => $this->course1->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'assignment_id' => $assignment->id,
            'score' => 88,
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('course', $data['grades'][0]);
        $this->assertArrayHasKey('assignment', $data['grades'][0]);
    }

    public function test_my_grades_requires_student_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(403);
    }

    public function test_my_grades_rounds_gpa_to_two_decimals(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course1->id,
            'score' => 93, // A = 4.0
            'max_score' => 100,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course2->id,
            'score' => 87, // B = 3.0
            'max_score' => 100,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-grades');

        $response->assertStatus(200);

        $data = $response->json('data');
        // GPA = (4.0 + 3.0) / 2 = 3.5
        $this->assertEquals(3.5, $data['gpa']);
        $this->assertIsFloat($data['gpa']);
    }

    // -------------------------------------------------------------------------
    // DASHBOARD Tests
    // -------------------------------------------------------------------------

    public function test_dashboard_returns_welcome_message_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Welcome Student!',
            ]);
    }

    public function test_dashboard_requires_student_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/student/dashboard');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Edge Cases
    // -------------------------------------------------------------------------

    public function test_my_courses_with_multiple_students_returns_only_own_courses(): void
    {
        $otherStudent = User::factory()->create(['role' => 'student']);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course2->id,
            'student_id' => $otherStudent->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-courses');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->course1->id, $data[0]['id']);
    }

    public function test_my_assignments_with_late_submissions(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course1->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $module = CourseModule::factory()->create(['course_id' => $this->course1->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course1->id,
            'module_id' => $module->id,
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'late',
            'is_late' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/student/my-assignments');

        $response->assertStatus(200);

        $data = $response->json('data');
        $lateAssignment = collect($data)->firstWhere('id', $assignment->id);
        $this->assertEquals('late', $lateAssignment['submission_status']);
    }
}