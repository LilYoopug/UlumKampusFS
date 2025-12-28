<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Course $course;

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
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing modules
    // -------------------------------------------------------------------------

    public function test_index_returns_modules_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        CourseModule::factory()->count(3)->create(['course_id' => $this->course->id]);

        $response = $this->getJson('/api/course-modules');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course modules retrieved successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/course-modules');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating modules
    // -------------------------------------------------------------------------

    public function test_store_creates_module_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $moduleData = [
            'course_id' => $this->course->id,
            'title' => 'Introduction Module',
            'description' => 'Introduction to the course',
            'content' => 'Module content here',
            'video_url' => 'https://example.com/video.mp4',
            'order' => 1,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/course-modules', $moduleData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Course module created successfully',
                'data' => [
                    'title' => 'Introduction Module',
                ],
            ]);

        $this->assertDatabaseHas('course_modules', [
            'title' => 'Introduction Module',
            'course_id' => $this->course->id,
        ]);
    }

    public function test_store_creates_module_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $moduleData = [
            'course_id' => $this->course->id,
            'title' => 'Module by Faculty',
            'description' => 'Created by faculty',
        ];

        $response = $this->postJson('/api/course-modules', $moduleData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $moduleData = [
            'course_id' => $this->course->id,
            'title' => 'Module by Student',
        ];

        $response = $this->postJson('/api/course-modules', $moduleData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/course-modules', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id', 'title']);
    }

    public function test_store_validates_course_exists(): void
    {
        Sanctum::actingAs($this->admin);

        $moduleData = [
            'course_id' => 99999,
            'title' => 'Module Title',
        ];

        $response = $this->postJson('/api/course-modules', $moduleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single module
    // -------------------------------------------------------------------------

    public function test_show_returns_module_for_authenticated_user(): void
    {
        $module = CourseModule::factory()->create([
            'course_id' => $this->course->id,
            'title' => 'Test Module',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/course-modules/' . $module->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course module retrieved successfully',
                'data' => [
                    'id' => $module->id,
                    'title' => 'Test Module',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_module(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/course-modules/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating modules
    // -------------------------------------------------------------------------

    public function test_update_modifies_module_for_admin(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'title' => 'Updated Module Title',
            'description' => 'Updated description',
        ];

        $response = $this->putJson('/api/course-modules/' . $module->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course module updated successfully',
                'data' => [
                    'title' => 'Updated Module Title',
                ],
            ]);

        $module->refresh();
        $this->assertEquals('Updated Module Title', $module->title);
    }

    public function test_update_modifies_module_for_faculty(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'title' => 'Faculty Updated',
        ];

        $response = $this->putJson('/api/course-modules/' . $module->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'title' => 'Student Update',
        ];

        $response = $this->putJson('/api/course-modules/' . $module->id, $updateData);

        $response->assertStatus(403);
    }

    public function test_update_sets_published_at_when_publishing(): void
    {
        $module = CourseModule::factory()->create([
            'course_id' => $this->course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'title' => $module->title,
            'is_published' => true,
        ];

        $response = $this->putJson('/api/course-modules/' . $module->id, $updateData);

        $response->assertStatus(200);

        $module->refresh();
        $this->assertTrue($module->is_published);
        $this->assertNotNull($module->published_at);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting modules
    // -------------------------------------------------------------------------

    public function test_delete_removes_module_for_admin(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/course-modules/' . $module->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('course_modules', ['id' => $module->id]);
    }

    public function test_delete_removes_module_for_faculty(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson('/api/course-modules/' . $module->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('course_modules', ['id' => $module->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/course-modules/' . $module->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // ASSIGNMENTS Tests - Getting assignments for a module
    // -------------------------------------------------------------------------

    public function test_assignments_returns_module_assignments(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);
        \App\Models\Assignment::factory()->count(2)->create([
            'course_id' => $this->course->id,
            'module_id' => $module->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/course-modules/' . $module->id . '/assignments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Module assignments retrieved successfully',
            ]);
    }

    public function test_assignments_returns_404_for_nonexistent_module(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/course-modules/99999/assignments');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // DISCUSSIONS Tests - Getting discussions for a module
    // -------------------------------------------------------------------------

    public function test_discussions_returns_module_discussions(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->course->id]);
        \App\Models\DiscussionThread::factory()->count(2)->create([
            'course_id' => $this->course->id,
            'module_id' => $module->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/course-modules/' . $module->id . '/discussions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Module discussions retrieved successfully',
            ]);
    }
}