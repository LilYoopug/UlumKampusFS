<?php

namespace Tests\Feature\Api;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Course $course;
    protected CourseModule $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        $facultyModel = Faculty::factory()->create();
        $majorModel = Major::factory()->create(['faculty_id' => $facultyModel->id]);

        $this->course = Course::factory()->create([
            'faculty_id' => $facultyModel->id,
            'major_id' => $majorModel->id,
            'instructor_id' => $this->faculty->id,
        ]);

        $this->module = CourseModule::factory()->create(['course_id' => $this->course->id]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing assignments
    // -------------------------------------------------------------------------

    public function test_index_returns_published_assignments_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Assignment::factory()->count(3)->create([
            'course_id' => $this->course->id,
            'is_published' => true,
        ]);
        Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/assignments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignments retrieved successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        $data = $response->json('data');
        // Should only return published assignments
        $this->assertCount(3, $data);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/assignments');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating assignments
    // -------------------------------------------------------------------------

    public function test_store_creates_assignment_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $assignmentData = [
            'course_id' => $this->course->id,
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'title' => 'Introduction Assignment',
            'description' => 'Complete the introduction',
            'instructions' => 'Write a brief introduction',
            'due_date' => now()->addDays(7)->toDateString(),
            'max_points' => 100,
            'submission_type' => 'text',
            'attempts_allowed' => 3,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/assignments', $assignmentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment created successfully',
                'data' => [
                    'title' => 'Introduction Assignment',
                ],
            ]);

        $this->assertDatabaseHas('assignments', [
            'title' => 'Introduction Assignment',
            'course_id' => $this->course->id,
        ]);
    }

    public function test_store_creates_assignment_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $assignmentData = [
            'course_id' => $this->course->id,
            'created_by' => $this->faculty->id,
            'title' => 'Faculty Assignment',
            'description' => 'Created by faculty',
        ];

        $response = $this->postJson('/api/assignments', $assignmentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $assignmentData = [
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
            'title' => 'Student Assignment',
        ];

        $response = $this->postJson('/api/assignments', $assignmentData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/assignments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id', 'created_by', 'title']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single assignment
    // -------------------------------------------------------------------------

    public function test_show_returns_assignment_for_authenticated_user(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'title' => 'Test Assignment',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/assignments/' . $assignment->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment retrieved successfully',
                'data' => [
                    'id' => $assignment->id,
                    'title' => 'Test Assignment',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_assignment(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/assignments/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating assignments
    // -------------------------------------------------------------------------

    public function test_update_modifies_assignment_for_admin(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'course_id' => $this->course->id,
            'created_by' => $this->admin->id,
            'title' => 'Updated Assignment Title',
            'description' => 'Updated description',
            'is_published' => true,
        ];

        $response = $this->putJson('/api/assignments/' . $assignment->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'data' => [
                    'title' => 'Updated Assignment Title',
                ],
            ]);

        $assignment->refresh();
        $this->assertEquals('Updated Assignment Title', $assignment->title);
        $this->assertNotNull($assignment->published_at);
    }

    public function test_update_modifies_assignment_for_faculty(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'course_id' => $this->course->id,
            'created_by' => $this->faculty->id,
            'title' => 'Faculty Updated',
        ];

        $response = $this->putJson('/api/assignments/' . $assignment->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
            'title' => 'Student Update',
        ];

        $response = $this->putJson('/api/assignments/' . $assignment->id, $updateData);

        $response->assertStatus(403);
    }

    public function test_update_sets_published_at_when_publishing(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'course_id' => $this->course->id,
            'created_by' => $this->admin->id,
            'title' => $assignment->title,
            'is_published' => true,
        ];

        $response = $this->putJson('/api/assignments/' . $assignment->id, $updateData);

        $response->assertStatus(200);

        $assignment->refresh();
        $this->assertTrue($assignment->is_published);
        $this->assertNotNull($assignment->published_at);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting assignments
    // -------------------------------------------------------------------------

    public function test_delete_removes_assignment_for_admin(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/assignments/' . $assignment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_delete_removes_assignment_for_faculty(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson('/api/assignments/' . $assignment->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/assignments/' . $assignment->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // SUBMISSIONS Tests - Getting submissions for an assignment
    // -------------------------------------------------------------------------

    public function test_submissions_returns_assignment_submissions(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);
        AssignmentSubmission::factory()->count(2)->create([
            'assignment_id' => $assignment->id,
            'student_id' => User::factory()->create(['role' => 'student'])->id,
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/assignments/' . $assignment->id . '/submissions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment submissions retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // SUBMIT Tests - Student submitting assignment
    // -------------------------------------------------------------------------

    public function test_submit_creates_submission_for_student(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_date' => now()->addDays(7),
        ]);

        Sanctum::actingAs($this->student);

        $submissionData = [
            'content' => 'This is my assignment submission',
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment submitted successfully',
            ]);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'submitted',
        ]);
    }

    public function test_submit_marks_late_submission(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_date' => now()->subDays(1),
        ]);

        Sanctum::actingAs($this->student);

        $submissionData = [
            'content' => 'Late submission',
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'status' => 'late',
            'is_late' => true,
        ]);
    }

    public function test_submit_increments_attempt_number(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'due_date' => now()->addDays(7),
        ]);

        // First submission
        Sanctum::actingAs($this->student);
        AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
        ]);

        $submissionData = [
            'content' => 'Second attempt',
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(201);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->student->id)
            ->orderBy('attempt_number', 'desc')
            ->first();

        $this->assertEquals(2, $submission->attempt_number);
    }

    public function test_submit_fails_for_non_student(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->admin);

        $submissionData = [
            'content' => 'Submission',
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MY SUBMISSION Tests - Getting student's own submission
    // -------------------------------------------------------------------------

    public function test_my_submission_returns_students_submission(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);
        $submission = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/assignments/' . $assignment->id . '/my-submission');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Submission retrieved successfully',
                'data' => [
                    'id' => $submission->id,
                ],
            ]);
    }

    public function test_my_submission_returns_404_when_no_submission(): void
    {
        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/assignments/' . $assignment->id . '/my-submission');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No submission found for this assignment',
            ]);
    }

    // -------------------------------------------------------------------------
    // PUBLISH Tests - Publishing an assignment
    // -------------------------------------------------------------------------

    public function test_publish_sets_assignment_as_published(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/publish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment published successfully',
            ]);

        $assignment->refresh();
        $this->assertTrue($assignment->is_published);
        $this->assertNotNull($assignment->published_at);
    }

    // -------------------------------------------------------------------------
    // UNPUBLISH Tests - Unpublishing an assignment
    // -------------------------------------------------------------------------

    public function test_unpublish_sets_assignment_as_unpublished(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/unpublish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Assignment unpublished',
            ]);

        $assignment->refresh();
        $this->assertFalse($assignment->is_published);
        $this->assertNull($assignment->published_at);
    }

    // -------------------------------------------------------------------------
    // File submission tests
    // -------------------------------------------------------------------------

    public function test_submit_with_file_url(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'submission_type' => 'file',
            'due_date' => now()->addDays(7),
        ]);

        Sanctum::actingAs($this->student);

        $submissionData = [
            'file_url' => 'https://example.com/files/assignment.pdf',
            'file_name' => 'assignment.pdf',
            'file_size' => 1024000,
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'file_name' => 'assignment.pdf',
        ]);
    }

    public function test_submit_with_link_url(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'submission_type' => 'link',
            'due_date' => now()->addDays(7),
        ]);

        Sanctum::actingAs($this->student);

        $submissionData = [
            'link_url' => 'https://github.com/user/repo',
        ];

        $response = $this->postJson('/api/assignments/' . $assignment->id . '/submit', $submissionData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $this->student->id,
            'link_url' => 'https://github.com/user/repo',
        ]);
    }
}