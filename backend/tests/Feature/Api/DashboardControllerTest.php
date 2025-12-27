<?php

namespace Tests\Feature\Api;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Dashboard Controller Tests
 *
 * Tests for dashboard analytics endpoints for different user roles:
 * - Student dashboard stats
 * - Faculty dashboard stats
 * - Prodi dashboard stats
 * - Management dashboard stats
 * - Grade distribution analytics
 * - Enrollment trends analytics
 */
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $course;
    protected Course $course2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        // Create faculty and major for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        // Update user with faculty/major associations
        $this->faculty->update(['faculty_id' => $this->facultyModel->id]);
        $this->student->update([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'enrollment_year' => 2023,
        ]);

        // Create courses taught by faculty
        $this->course = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
            'credit_hours' => 3,
            'is_active' => true,
        ]);

        $this->course2 = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
            'credit_hours' => 4,
            'is_active' => true,
        ]);

        // Enroll student in courses
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
            'enrolled_at' => now()->subMonths(2),
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course2->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
            'enrolled_at' => now()->subMonths(1),
        ]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Auto-routing based on user role
    // -------------------------------------------------------------------------

    public function test_index_routes_to_student_stats_for_student_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'total_courses',
                    'total_sks',
                    'gpa',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('student', $data['role']);
    }

    public function test_index_routes_to_faculty_stats_for_faculty_role(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'total_courses',
                    'total_students',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('faculty', $data['role']);
    }

    public function test_index_routes_to_management_stats_for_admin_role(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'users',
                    'courses',
                    'enrollments',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('management', $data['role']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STUDENT STATS Tests
    // -------------------------------------------------------------------------

    public function test_student_stats_returns_correct_data(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/student');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'total_courses',
                    'total_sks',
                    'gpa',
                    'pending_assignments',
                    'submitted_assignments',
                    'graded_assignments',
                    'upcoming_assignments',
                    'recent_grades',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('student', $data['role']);
        $this->assertEquals(2, $data['total_courses']);
        $this->assertEquals(7, $data['total_sks']); // 3 + 4 credit hours
        $this->assertEquals(0.0, $data['gpa']); // No grades yet
    }

    public function test_student_stats_requires_student_role(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/student');

        $response->assertStatus(403);
    }

    public function test_student_stats_calculates_gpa_correctly(): void
    {
        // Create grades for the student
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 95.00,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course2->id,
            'grade' => 85.00,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/student');

        $data = $response->json('data');
        // (4.0 + 3.0) / 2 = 3.5
        $this->assertEquals(3.5, $data['gpa']);
    }

    public function test_student_stats_counts_pending_assignments(): void
    {
        // Create a published assignment that's due in the future
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_date' => now()->addDays(5),
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/student');

        $data = $response->json('data');
        $this->assertEquals(1, $data['pending_assignments']);
    }

    public function test_student_stats_excludes_submitted_assignments_from_pending(): void
    {
        // Create an assignment and submit it
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_date' => now()->addDays(5),
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/student');

        $data = $response->json('data');
        $this->assertEquals(0, $data['pending_assignments']);
        $this->assertEquals(1, $data['submitted_assignments']);
    }

    // -------------------------------------------------------------------------
    // FACULTY STATS Tests
    // -------------------------------------------------------------------------

    public function test_faculty_stats_returns_correct_data(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/faculty');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'total_courses',
                    'active_courses',
                    'total_students',
                    'assignments_pending_grading',
                    'upcoming_classes',
                    'course_grades',
                    'total_assignments',
                    'published_assignments',
                    'total_submissions',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('faculty', $data['role']);
        $this->assertEquals(2, $data['total_courses']);
        $this->assertEquals(2, $data['active_courses']);
        $this->assertEquals(2, $data['total_students']); // Two enrollments for one student
    }

    public function test_faculty_stats_requires_faculty_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/faculty');

        $response->assertStatus(403);
    }

    public function test_faculty_stats_counts_pending_grading(): void
    {
        // Create assignment and submission
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/faculty');

        $data = $response->json('data');
        $this->assertEquals(1, $data['assignments_pending_grading']);
    }

    public function test_faculty_stats_excludes_graded_from_pending(): void
    {
        // Create assignment and graded submission
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/faculty');

        $data = $response->json('data');
        $this->assertEquals(0, $data['assignments_pending_grading']);
    }

    // -------------------------------------------------------------------------
    // PRODI STATS Tests
    // -------------------------------------------------------------------------

    public function test_prodi_stats_returns_correct_data_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/prodi?faculty_id=' . $this->facultyModel->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'faculty_id',
                    'faculty_name',
                    'total_students',
                    'total_courses',
                    'active_courses',
                    'average_gpa',
                    'total_majors',
                    'majors_data',
                    'total_enrollments',
                    'active_enrollments',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('prodi', $data['role']);
        $this->assertEquals($this->facultyModel->id, $data['faculty_id']);
        $this->assertEquals(1, $data['total_students']);
        $this->assertEquals(2, $data['total_courses']);
    }

    public function test_prodi_stats_returns_correct_data_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/prodi?faculty_id=' . $this->facultyModel->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_prodi_stats_requires_admin_or_faculty_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/dashboard/prodi?faculty_id=' . $this->facultyModel->id);

        $response->assertStatus(403);
    }

    public function test_prodi_stats_requires_faculty_id_parameter(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/prodi');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Faculty ID is required',
            ]);
    }

    public function test_prodi_stats_validates_faculty_id_exists(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/prodi?faculty_id=99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // MANAGEMENT STATS Tests
    // -------------------------------------------------------------------------

    public function test_management_stats_returns_correct_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/management');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'users' => [
                        'total',
                        'students',
                        'faculty',
                        'admins',
                    ],
                    'courses' => [
                        'total',
                        'active',
                    ],
                    'enrollments' => [
                        'total',
                        'active',
                        'completed',
                    ],
                    'faculties' => [
                        'total',
                        'active',
                    ],
                    'majors' => [
                        'total',
                        'active',
                    ],
                    'assignments' => [
                        'total',
                        'submissions',
                        'graded',
                    ],
                    'grades' => [
                        'total',
                        'average',
                    ],
                    'students_by_year',
                    'courses_by_faculty',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('management', $data['role']);
        $this->assertGreaterThanOrEqual(1, $data['users']['total']);
        $this->assertGreaterThanOrEqual(1, $data['courses']['total']);
    }

    public function test_management_stats_requires_admin_role(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/dashboard/management');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GRADE DISTRIBUTION Tests
    // -------------------------------------------------------------------------

    public function test_grade_distribution_returns_correct_data(): void
    {
        // Create grades with different values
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92.00,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85.00,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 75.00,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/grade-distribution?course_id=' . $this->course->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'course_id',
                    'total_grades',
                    'distribution' => ['A', 'B', 'C', 'D', 'F'],
                    'percentages' => ['A', 'B', 'C', 'D', 'F'],
                    'average_grade',
                    'highest_grade',
                    'lowest_grade',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals($this->course->id, $data['course_id']);
        $this->assertEquals(3, $data['total_grades']);
        $this->assertEquals(1, $data['distribution']['A']);
        $this->assertEquals(1, $data['distribution']['B']);
        $this->assertEquals(1, $data['distribution']['C']);
        $this->assertEquals(92.0, $data['highest_grade']);
        $this->assertEquals(75.0, $data['lowest_grade']);
    }

    public function test_grade_distribution_validates_course_id(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/grade-distribution');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_grade_distribution_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard/grade-distribution?course_id=' . $this->course->id);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // ENROLLMENT TRENDS Tests
    // -------------------------------------------------------------------------

    public function test_enrollment_trends_returns_monthly_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?period=monthly');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'faculty_id',
                    'major_id',
                    'trends',
                    'total_enrollments',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals('monthly', $data['period']);
    }

    public function test_enrollment_trends_returns_semesterly_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?period=semesterly');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals('semesterly', $data['period']);
    }

    public function test_enrollment_trends_returns_yearly_data(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?period=yearly');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals('yearly', $data['period']);
    }

    public function test_enrollment_trends_filters_by_faculty(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?faculty_id=' . $this->facultyModel->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals($this->facultyModel->id, $data['faculty_id']);
    }

    public function test_enrollment_trends_filters_by_major(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?major_id=' . $this->majorModel->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertEquals($this->majorModel->id, $data['major_id']);
    }

    public function test_enrollment_trends_validates_period_parameter(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/enrollment-trends?period=invalid');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_enrollment_trends_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard/enrollment-trends');

        $response->assertStatus(401);
    }
}