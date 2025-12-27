<?php

namespace Tests\Feature\Api;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use App\Models\Grade;
use App\Models\CourseModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubmissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $otherStudent;
    protected User $instructor;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $course;
    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);
        $this->instructor = User::factory()->create(['role' => 'faculty']);

        // Create faculty and major for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        // Create course and assignment for testing
        $this->course = Course::factory()->create([
            'instructor_id' => $this->instructor->id,
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'is_active' => true,
        ]);

        // Enroll student in the course
        CourseEnrollment::factory()->create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        // Create published assignment
        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER INDEX Tests - Student's submissions
    // =========================================================================

    public function test_index_returns_submissions_for_authenticated_student(): void
    {
        AssignmentSubmission::factory()->count(3)->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submissions retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_index_returns_empty_list_for_student_with_no_submissions(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submissions retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_index_fails_for_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/submissions');

        $response->assertStatus(401);
    }

    public function test_index_only_returns_own_submissions(): void
    {
        // Create submissions for this student
        AssignmentSubmission::factory()->count(2)->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Create submissions for another student
        AssignmentSubmission::factory()->count(3)->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $submission) {
            $this->assertEquals($this->student->id, $submission['student_id']);
        }
    }

    public function test_index_orders_submissions_by_submitted_at_descending(): void
    {
        $submission1 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'submitted_at' => now()->subDays(3),
            'status' => 'submitted',
        ]);

        $submission2 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'submitted_at' => now()->subDays(1),
            'status' => 'submitted',
        ]);

        $submission3 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'submitted_at' => now()->subDays(2),
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($submission2->id, $data[0]['id']);
        $this->assertEquals($submission3->id, $data[1]['id']);
        $this->assertEquals($submission1->id, $data[2]['id']);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER SHOW Tests - Single submission
    // =========================================================================

    public function test_show_returns_submission_for_owner_student(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/submissions/{$submission->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submission retrieved successfully',
                'data' => [
                    'id' => $submission->id,
                    'student_id' => $this->student->id,
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_submission(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions/99999');

        $response->assertStatus(404);
    }

    public function test_show_fails_for_unauthenticated_user(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
        ]);

        $response = $this->getJson("/api/submissions/{$submission->id}");

        $response->assertStatus(401);
    }

    public function test_show_fails_for_non_owner_student(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->otherStudent->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/submissions/{$submission->id}");

        $response->assertStatus(404);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER UPDATE Tests - Edit draft submissions
    // =========================================================================

    public function test_update_modifies_draft_submission_for_owner(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
            'content' => 'Original content',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
            'file_url' => 'https://example.com/file.pdf',
            'file_name' => 'assignment.pdf',
            'file_size' => 1024,
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submission updated successfully',
            ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'content' => 'Updated content',
            'file_url' => 'https://example.com/file.pdf',
        ]);
    }

    public function test_update_fails_for_submitted_submission(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Can only edit draft submissions',
            ]);
    }

    public function test_update_fails_for_graded_submission(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'submitted_at' => now()->subDays(1),
            'graded_at' => now(),
            'grade' => 85.50,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_update_fails_for_late_submission(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'late',
            'submitted_at' => now(),
            'is_late' => true,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_update_fails_for_non_owner(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'draft',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(404);
    }

    public function test_update_validates_file_url_format(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'file_url' => 'not-a-valid-url',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_url']);
    }

    public function test_update_validates_link_url_format(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'link_url' => 'not-a-valid-url',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['link_url']);
    }

    public function test_update_allows_partial_updates(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'draft',
            'content' => 'Original content',
            'file_url' => 'https://example.com/original.pdf',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content only',
        ];

        $response = $this->putJson("/api/submissions/{$submission->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'content' => 'Updated content only',
            'file_url' => 'https://example.com/original.pdf',
        ]);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER BY ASSIGNMENT Tests - Faculty/Admin view
    // =========================================================================

    public function test_by_assignment_returns_submissions_for_admin(): void
    {
        AssignmentSubmission::factory()->count(3)->create([
            'assignment_id' => $this->assignment->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment submissions retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_by_assignment_returns_submissions_for_faculty(): void
    {
        AssignmentSubmission::factory()->count(2)->create([
            'assignment_id' => $this->assignment->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_by_assignment_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");

        $response->assertStatus(403);
    }

    public function test_by_assignment_returns_empty_list_for_no_submissions(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function test_by_assignment_orders_by_submitted_at_descending(): void
    {
        $submission1 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'submitted_at' => now()->subDays(3),
            'status' => 'submitted',
        ]);

        $submission2 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'submitted_at' => now()->subDays(1),
            'status' => 'submitted',
        ]);

        $submission3 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'submitted_at' => now()->subDays(2),
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($submission2->id, $data[0]['id']);
        $this->assertEquals($submission3->id, $data[1]['id']);
        $this->assertEquals($submission1->id, $data[2]['id']);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER GRADE Tests - Grading submissions
    // =========================================================================

    public function test_grade_submits_grade_for_admin(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => 85.50,
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submission graded successfully',
            ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'grade' => 85.50,
            'status' => 'graded',
            'graded_by' => $this->admin->id,
        ]);
    }

    public function test_grade_submits_grade_for_faculty(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->faculty);

        $gradeData = [
            'grade' => 92.00,
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'grade' => 92.00,
            'status' => 'graded',
            'graded_by' => $this->faculty->id,
        ]);
    }

    public function test_grade_creates_grade_record(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => 88.50,
        ];

        $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $this->assertDatabaseHas('grades', [
            'user_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => $this->course->id,
            'grade' => 88.50,
        ]);
    }

    public function test_grade_updates_existing_grade_record(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'submitted_at' => now()->subDays(2),
            'graded_at' => now()->subDay(),
            'grade' => 75.00,
            'graded_by' => $this->admin->id,
        ]);

        Grade::factory()->create([
            'user_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => $this->course->id,
            'grade' => 75.00,
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => 90.00,
        ];

        $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $this->assertDatabaseHas('grades', [
            'user_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'grade' => 90.00,
        ]);
    }

    public function test_grade_fails_for_student(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->otherStudent->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->student);

        $gradeData = [
            'grade' => 85.00,
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(403);
    }

    public function test_grade_validates_grade_is_required(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);
    }

    public function test_grade_validates_grade_is_numeric(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => 'not-a-number',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);
    }

    public function test_grade_validates_grade_is_not_negative(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => -10.00,
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['grade']);
    }

    public function test_grade_allows_zero_grade(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $gradeData = [
            'grade' => 0.00,
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/grade", $gradeData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'grade' => 0.00,
        ]);
    }

    // =========================================================================
    // SUBMISSION CONTROLLER FEEDBACK Tests - Providing feedback
    // =========================================================================

    public function test_feedback_adds_feedback_for_admin(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $feedbackData = [
            'feedback' => 'Great work on this assignment!',
            'instructor_notes' => 'Note for internal reference',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Feedback added successfully',
            ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'feedback' => 'Great work on this assignment!',
            'instructor_notes' => 'Note for internal reference',
        ]);
    }

    public function test_feedback_adds_feedback_for_faculty(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
        ]);

        Sanctum::actingAs($this->faculty);

        $feedbackData = [
            'feedback' => 'Please review your approach.',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'feedback' => 'Please review your approach.',
        ]);
    }

    public function test_feedback_updates_grade_record_when_grade_exists(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'grade' => 85.00,
            'graded_at' => now()->subDay(),
        ]);

        $grade = Grade::factory()->create([
            'user_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
            'course_id' => $this->course->id,
            'grade' => 85.00,
            'comments' => 'Original comments',
        ]);

        Sanctum::actingAs($this->admin);

        $feedbackData = [
            'feedback' => 'Updated feedback for the student',
        ];

        $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'comments' => 'Updated feedback for the student',
        ]);
    }

    public function test_feedback_does_not_create_grade_record_when_no_grade(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'grade' => null,
        ]);

        Sanctum::actingAs($this->admin);

        $feedbackData = [
            'feedback' => 'Feedback before grading',
        ];

        $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $this->assertDatabaseMissing('grades', [
            'user_id' => $this->student->id,
            'assignment_id' => $this->assignment->id,
        ]);
    }

    public function test_feedback_fails_for_student(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->student);

        $feedbackData = [
            'feedback' => 'Trying to add my own feedback',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $response->assertStatus(403);
    }

    public function test_feedback_validates_feedback_is_required(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $feedbackData = [
            'instructor_notes' => 'Only notes, no feedback',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['feedback']);
    }

    public function test_feedback_allows_only_feedback_without_instructor_notes(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        $feedbackData = [
            'feedback' => 'Feedback without notes',
        ];

        $response = $this->postJson("/api/submissions/{$submission->id}/feedback", $feedbackData);

        $response->assertStatus(200);
    }

    // =========================================================================
    // ASSIGNMENT CONTROLLER SUBMIT Tests - Submit assignment
    // =========================================================================

    public function test_submit_creates_submission_with_text_content(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'This is my assignment submission text.',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment submitted successfully',
            ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'content' => 'This is my assignment submission text.',
            'status' => 'submitted',
            'attempt_number' => 1,
        ]);
    }

    public function test_submit_creates_submission_with_file(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'file_url' => 'https://example.com/my-assignment.pdf',
            'file_name' => 'my-assignment.pdf',
            'file_size' => 5242880, // 5MB
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'file_url' => 'https://example.com/my-assignment.pdf',
            'file_name' => 'my-assignment.pdf',
            'file_size' => 5242880,
        ]);
    }

    public function test_submit_creates_submission_with_link(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'link_url' => 'https://github.com/user/project',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'link_url' => 'https://github.com/user/project',
        ]);
    }

    public function test_submit_creates_submission_with_mixed_content(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'See the attached file and link.',
            'file_url' => 'https://example.com/file.pdf',
            'file_name' => 'file.pdf',
            'link_url' => 'https://github.com/user/repo',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201);
    }

    public function test_submit_marks_as_late_when_after_due_date(): void
    {
        $lateAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'due_date' => now()->subDays(1),
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'Late submission',
        ];

        $response = $this->postJson("/api/assignments/{$lateAssignment->id}/submit", $submitData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $lateAssignment->id,
            'student_id' => $this->student->id,
            'status' => 'late',
            'is_late' => true,
        ]);
    }

    public function test_submit_increments_attempt_number(): void
    {
        AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'attempt_number' => 1,
            'submitted_at' => now()->subDays(1),
        ]);

        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'Second attempt',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 2,
        ]);
    }

    public function test_submit_allows_third_attempt(): void
    {
        AssignmentSubmission::factory()->count(2)->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'attempt_number' => 1,
        ]);

        AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->where('student_id', $this->student->id)
            ->update(['attempt_number' => 2]);

        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'Third attempt',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 3,
        ]);
    }

    public function test_submit_fails_for_non_student(): void
    {
        Sanctum::actingAs($this->faculty);

        $submitData = [
            'content' => 'Faculty trying to submit',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(403);
    }

    public function test_submit_validates_file_url_format(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'file_url' => 'not-a-valid-url',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file_url']);
    }

    public function test_submit_validates_link_url_format(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'link_url' => 'not-a-valid-url',
        ];

        $response = $this->postJson("/api/assignments/{$this->assignment->id}/submit", $submitData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['link_url']);
    }

    public function test_submit_returns_404_for_nonexistent_assignment(): void
    {
        Sanctum::actingAs($this->student);

        $submitData = [
            'content' => 'Test submission',
        ];

        $response = $this->postJson('/api/assignments/99999/submit', $submitData);

        $response->assertStatus(404);
    }

    // =========================================================================
    // ASSIGNMENT CONTROLLER MY SUBMISSION Tests - Get own submission
    // =========================================================================

    public function test_my_submission_returns_latest_submission(): void
    {
        $submission1 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => 'submitted',
            'submitted_at' => now()->subDays(2),
        ]);

        $submission2 = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 2,
            'status' => 'submitted',
            'submitted_at' => now()->subDay(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/assignments/{$this->assignment->id}/my-submission");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submission retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertEquals($submission2->id, $data['id']);
    }

    public function test_my_submission_returns_404_when_no_submission_exists(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/assignments/{$this->assignment->id}/my-submission");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No submission found for this assignment',
            ]);
    }

    public function test_my_submission_returns_404_for_nonexistent_assignment(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/assignments/99999/my-submission');

        $response->assertStatus(404);
    }

    public function test_my_submission_fails_for_non_student(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/assignments/{$this->assignment->id}/my-submission");

        $response->assertStatus(403);
    }

    public function test_my_submission_includes_graded_data(): void
    {
        $grader = User::factory()->create(['role' => 'faculty']);
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'graded',
            'grade' => 88.50,
            'feedback' => 'Excellent work!',
            'graded_by' => $grader->id,
            'graded_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/assignments/{$this->assignment->id}/my-submission");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($submission->id, $data['id']);
        $this->assertEquals(88.50, $data['grade']);
        $this->assertEquals('Excellent work!', $data['feedback']);
        $this->assertTrue($data['is_graded']);
    }

    // =========================================================================
    // ADDITIONAL EDGE CASES AND INTEGRATION TESTS
    // =========================================================================

    public function test_submission_can_be_graded_and_feedback_added_separately(): void
    {
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->admin);

        // First, grade the submission
        $this->postJson("/api/submissions/{$submission->id}/grade", [
            'grade' => 85.00,
        ]);

        // Then, add feedback
        $this->postJson("/api/submissions/{$submission->id}/feedback", [
            'feedback' => 'Great work!',
        ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'grade' => 85.00,
            'feedback' => 'Great work!',
            'status' => 'graded',
        ]);
    }

    public function test_late_submission_shows_correct_flags(): void
    {
        $lateAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'due_date' => now()->subDays(1),
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/assignments/{$lateAssignment->id}/submit", [
            'content' => 'Late submission',
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $lateAssignment->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertEquals('late', $submission->status);
        $this->assertTrue($submission->is_late);
        $this->assertNotNull($submission->late_submission_at);
    }

    public function test_submission_with_no_due_date_is_marked_submitted(): void
    {
        $noDueDateAssignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'due_date' => null,
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/assignments/{$noDueDateAssignment->id}/submit", [
            'content' => 'Submission with no due date',
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $noDueDateAssignment->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertEquals('submitted', $submission->status);
        $this->assertFalse($submission->is_late);
    }

    public function test_student_can_see_own_submissions_across_multiple_assignments(): void
    {
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now()->subDays(1),
        ]);

        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment2->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/submissions');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_faculty_can_view_all_submissions_for_their_course(): void
    {
        // Create another assignment in the same course
        $assignment2 = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->instructor->id,
            'is_published' => true,
        ]);

        AssignmentSubmission::factory()->count(3)->create([
            'assignment_id' => $this->assignment->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        AssignmentSubmission::factory()->count(2)->create([
            'assignment_id' => $assignment2->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($this->instructor);

        $response1 = $this->getJson("/api/submissions/assignment/{$this->assignment->id}");
        $response2 = $this->getJson("/api/submissions/assignment/{$assignment2->id}");

        $this->assertCount(3, $response1->json('data'));
        $this->assertCount(2, $response2->json('data'));
    }
}