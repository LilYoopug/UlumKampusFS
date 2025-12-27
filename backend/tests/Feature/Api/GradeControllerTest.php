<?php

namespace Tests\Feature\Api;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $instructor;
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
        $this->instructor = User::factory()->create(['role' => 'faculty']);

        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        $this->course = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
        ]);

        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
        ]);

        // Enroll the student in the course
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing grades for students
    // -------------------------------------------------------------------------

    public function test_index_returns_student_grades(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'B',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/grades');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Grades retrieved successfully',
            ]);
    }

    public function test_index_filters_grades_by_course(): void
    {
        $otherCourse = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $otherCourse->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $otherCourse->id,
            'grade' => 92,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/grades?course_id={$this->course->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $grade) {
            $this->assertEquals($this->course->id, $grade['course_id']);
        }
        $this->assertCount(1, $data);
    }

    public function test_index_filters_grades_by_assignment(): void
    {
        $otherAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $this->assignment->id,
            'grade' => 85,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $otherAssignment->id,
            'grade' => 92,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/grades?assignment_id={$this->assignment->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $grade) {
            $this->assertEquals($this->assignment->id, $grade['assignment_id']);
        }
        $this->assertCount(1, $data);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/grades');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single grade
    // -------------------------------------------------------------------------

    public function test_show_returns_grade_for_owner(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/grades/{$grade->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $grade->id,
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_grade(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/grades/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // BY COURSE Tests - Getting grades by course
    // -------------------------------------------------------------------------

    public function test_by_course_returns_grades_for_admin(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/grades/course/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course grades retrieved successfully',
            ]);
    }

    public function test_by_course_returns_grades_for_faculty(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/grades/course/{$this->course->id}");

        $response->assertStatus(200);
    }

    public function test_by_course_filters_by_student(): void
    {
        $otherStudent = User::factory()->create(['role' => 'student']);
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $otherStudent->id,
            'status' => 'enrolled',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Grade::factory()->create([
            'user_id' => $otherStudent->id,
            'course_id' => $this->course->id,
            'grade' => 92,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/grades/course/{$this->course->id}?student_id={$this->student->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $grade) {
            $this->assertEquals($this->student->id, $grade['user_id']);
        }
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // BY ASSIGNMENT Tests - Getting grades by assignment
    // -------------------------------------------------------------------------

    public function test_by_assignment_returns_grades_for_admin(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $this->assignment->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/grades/assignment/{$this->assignment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment grades retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // BY STUDENT Tests - Getting grades by student
    // -------------------------------------------------------------------------

    public function test_by_student_returns_grades_for_admin(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/grades/student/{$this->student->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Student grades retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating grades
    // -------------------------------------------------------------------------

    public function test_store_creates_grade_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $gradeData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'assignment_id' => $this->assignment->id,
            'grade' => 85.5,
            'comments' => 'Good work on this assignment.',
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Grade created successfully',
            ]);

        $this->assertDatabaseHas('grades', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85.50,
        ]);
    }

    public function test_store_creates_grade_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $gradeData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92.0,
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('grades', [
            'user_id' => $this->student->id,
            'grade' => 92.00,
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $gradeData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/grades', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'course_id', 'grade']);
    }

    public function test_store_validates_grade_range(): void
    {
        Sanctum::actingAs($this->admin);

        $gradeData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 105, // Over 100
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);
    }

    public function test_store_validates_student_enrollment(): void
    {
        $unenrolledStudent = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'user_id' => $unenrolledStudent->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(422);
    }

    public function test_store_auto_calculates_grade_letter(): void
    {
        Sanctum::actingAs($this->admin);

        $gradeData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
        ];

        $response = $this->postJson('/api/grades', $gradeData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('grades', [
            'user_id' => $this->student->id,
            'grade' => 92.00,
            'grade_letter' => 'A',
        ]);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating grades
    // -------------------------------------------------------------------------

    public function test_update_modifies_grade_for_admin(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 75,
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 88,
            'comments' => 'Improved work.',
        ];

        $response = $this->putJson("/api/grades/{$grade->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Grade updated successfully',
            ]);

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'grade' => 88.00,
        ]);
    }

    public function test_update_modifies_grade_for_faculty(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 75,
        ]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
        ];

        $response = $this->putJson("/api/grades/{$grade->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'grade' => 92.00,
        ]);
    }

    public function test_update_fails_for_student(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 75,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
        ];

        $response = $this->putJson("/api/grades/{$grade->id}", $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting grades
    // -------------------------------------------------------------------------

    public function test_destroy_removes_grade_for_admin(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/grades/{$grade->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_destroy_removes_grade_for_faculty(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson("/api/grades/{$grade->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('grades', ['id' => $grade->id]);
    }

    public function test_destroy_fails_for_student(): void
    {
        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/grades/{$grade->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    }

    // -------------------------------------------------------------------------
    // MY GRADES Tests - Student viewing own grades with GPA
    // -------------------------------------------------------------------------

    public function test_my_grades_returns_grades_with_gpa(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
            'grade_letter' => 'A',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'B',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/grades/my-grades');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'My grades retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertEquals(3.5, $data['gpa']); // (4.0 + 3.0) / 2
        $this->assertEquals(2, $data['total_assignments']);
        $this->assertCount(2, $data['grades']);
    }

    public function test_my_grades_calculates_correct_gpa_for_all_grades(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
            'grade_letter' => 'A',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 82,
            'grade_letter' => 'B',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 72,
            'grade_letter' => 'C',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 62,
            'grade_letter' => 'D',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/grades/my-grades');

        $data = $response->json('data');
        $this->assertEquals(2.5, $data['gpa']); // (4.0 + 3.0 + 2.0 + 1.0) / 4
    }

    // -------------------------------------------------------------------------
    // DISTRIBUTION Tests - Grade distribution by course
    // -------------------------------------------------------------------------

    public function test_distribution_returns_grade_counts(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 92,
            'grade_letter' => 'A',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'B',
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 75,
            'grade_letter' => 'C',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/grades/distribution/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Grade distribution retrieved successfully',
                'data' => [
                    'A' => 1,
                    'B' => 1,
                    'C' => 1,
                    'D' => 0,
                    'F' => 0,
                    'total' => 3,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // ANALYTICS Tests
    // -------------------------------------------------------------------------

    public function test_analytics_by_faculty_returns_faculty_stats(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'B',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/grades/analytics/faculty');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Faculty grade analytics retrieved successfully',
            ]);
    }

    public function test_analytics_by_course_returns_course_stats(): void
    {
        Grade::factory()->create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'grade' => 85,
            'grade_letter' => 'B',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/grades/analytics/course');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course grade analytics retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey($this->course->code, $data);
    }
}