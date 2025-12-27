<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnrollmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $otherStudent;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);

        // Create faculty and major for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        // Create a test course
        $this->course = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing student's own enrollments
    // -------------------------------------------------------------------------

    public function test_index_returns_student_enrollments(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Enrollments retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->student->id, $data[0]['student_id']);
    }

    public function test_index_returns_enrollments_with_course_details(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'course_id',
                        'student_id',
                        'status',
                        'enrolled_at',
                        'completed_at',
                        'dropped_at',
                        'grade',
                        'notes',
                        'withdrawal_reason',
                        'course',
                        'is_active',
                        'is_completed',
                        'is_dropped',
                        'is_pending',
                    ],
                ],
            ]);
    }

    public function test_index_returns_empty_array_for_student_with_no_enrollments(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_index_returns_all_status_enrollments_for_student(): void
    {
        $course2 = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);
        $course3 = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);
        $course4 = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course2->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course3->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course4->id,
            'student_id' => $this->student->id,
            'status' => 'completed',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(4, $data);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(401);
    }

    public function test_index_fails_for_non_student_role(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a specific enrollment
    // -------------------------------------------------------------------------

    public function test_show_returns_enrollment_for_owning_student(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Enrollment retrieved successfully',
                'data' => [
                    'id' => $enrollment->id,
                    'student_id' => $this->student->id,
                ],
            ]);
    }

    public function test_show_returns_enrollment_with_course_details(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'course_id',
                    'student_id',
                    'status',
                    'enrolled_at',
                    'completed_at',
                    'dropped_at',
                    'grade',
                    'notes',
                    'withdrawal_reason',
                    'course',
                    'is_active',
                    'is_completed',
                    'is_dropped',
                    'is_pending',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_enrollment(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments/99999');

        $response->assertStatus(404);
    }

    public function test_show_fails_for_other_students_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(401);
    }

    public function test_show_fails_for_non_student_role(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // BY COURSE Tests - Getting enrollments for a specific course
    // -------------------------------------------------------------------------

    public function test_by_course_returns_enrollments_for_admin(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course enrollments retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_by_course_returns_enrollments_for_faculty(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_by_course_returns_enrollments_with_student_details(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'course_id',
                        'student_id',
                        'status',
                        'student',
                    ],
                ],
            ]);
    }

    public function test_by_course_returns_empty_array_for_course_with_no_enrollments(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    public function test_by_course_returns_all_status_enrollments(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => User::factory()->create(['role' => 'student']),
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_by_course_requires_authentication(): void
    {
        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(401);
    }

    public function test_by_course_fails_for_student_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // APPROVE Tests - Approving a pending enrollment
    // -------------------------------------------------------------------------

    public function test_approve_changes_status_to_enrolled(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
            'enrolled_at' => null,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Enrollment approved successfully',
                'data' => [
                    'id' => $enrollment->id,
                    'status' => 'enrolled',
                ],
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'id' => $enrollment->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_approve_sets_enrolled_at_timestamp(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
            'enrolled_at' => null,
        ]);

        Sanctum::actingAs($this->admin);

        $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $enrollment->refresh();
        $this->assertNotNull($enrollment->enrolled_at);
    }

    public function test_approve_increments_course_current_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $initialEnrollment = $this->course->current_enrollment;

        Sanctum::actingAs($this->admin);

        $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $this->course->refresh();
        $this->assertEquals($initialEnrollment + 1, $this->course->current_enrollment);
    }

    public function test_approve_works_for_faculty(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_approve_returns_enrollment_with_course_and_student(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'course',
                    'student',
                ],
            ]);
    }

    public function test_approve_fails_for_nonexistent_enrollment(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/enrollments/99999/approve');

        $response->assertStatus(404);
    }

    public function test_approve_requires_authentication(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(401);
    }

    public function test_approve_fails_for_student_role(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // REJECT Tests - Rejecting a pending enrollment
    // -------------------------------------------------------------------------

    public function test_reject_changes_status_to_rejected(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Enrollment rejected',
                'data' => [
                    'id' => $enrollment->id,
                    'status' => 'rejected',
                ],
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'id' => $enrollment->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_does_not_increment_course_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $initialEnrollment = $this->course->current_enrollment;

        Sanctum::actingAs($this->admin);

        $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $this->course->refresh();
        $this->assertEquals($initialEnrollment, $this->course->current_enrollment);
    }

    public function test_reject_works_for_faculty(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_reject_returns_enrollment_with_course_and_student(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'course',
                    'student',
                ],
            ]);
    }

    public function test_reject_fails_for_nonexistent_enrollment(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/enrollments/99999/reject');

        $response->assertStatus(404);
    }

    public function test_reject_requires_authentication(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(401);
    }

    public function test_reject_fails_for_student_role(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting an enrollment
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('course_enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    public function test_destroy_decrements_course_enrollment_for_enrolled_status(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $initialEnrollment = $this->course->current_enrollment;

        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $this->course->refresh();
        $this->assertEquals($initialEnrollment - 1, $this->course->current_enrollment);
    }

    public function test_destroy_does_not_decrement_course_enrollment_for_non_enrolled_status(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $initialEnrollment = $this->course->current_enrollment;

        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $this->course->refresh();
        $this->assertEquals($initialEnrollment, $this->course->current_enrollment);
    }

    public function test_destroy_works_for_faculty(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(204);
    }

    public function test_destroy_deletes_dropped_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('course_enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    public function test_destroy_deletes_rejected_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'rejected',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('course_enrollments', [
            'id' => $enrollment->id,
        ]);
    }

    public function test_destroy_fails_for_nonexistent_enrollment(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/enrollments/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(401);
    }

    public function test_destroy_fails_for_student_role(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // EDGE CASE Tests
    // -------------------------------------------------------------------------

    public function test_index_returns_correct_enrollment_flags(): void
    {
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/enrollments');

        $response->assertStatus(200);
        $data = $response->json('data.0');
        $this->assertTrue($data['is_active']);
        $this->assertFalse($data['is_completed']);
        $this->assertFalse($data['is_dropped']);
        $this->assertFalse($data['is_pending']);
    }

    public function test_by_course_returns_only_course_enrollments(): void
    {
        $otherCourse = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
        ]);

        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $otherCourse->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/enrollments/course/{$this->course->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->course->id, $data[0]['course_id']);
    }

    public function test_approve_handles_already_enrolled_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
            'enrolled_at' => now()->subDays(5),
        ]);

        $initialEnrollment = $this->course->current_enrollment;

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/approve");

        $response->assertStatus(200);

        $this->course->refresh();
        // Should still increment again as per current implementation
        $this->assertEquals($initialEnrollment + 1, $this->course->current_enrollment);
    }

    public function test_reject_handles_already_rejected_enrollment(): void
    {
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'rejected',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/enrollments/{$enrollment->id}/reject");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'rejected',
                ],
            ]);
    }
}