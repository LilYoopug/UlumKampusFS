<?php

namespace Tests\Feature\Api;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $courseModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        // Create faculty, major, and course for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);
        $this->courseModel = Course::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing announcements
    // -------------------------------------------------------------------------

    public function test_index_returns_announcements_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Announcement::factory()->count(3)->create(['is_published' => true]);

        $response = $this->getJson('/api/announcements');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_index_filters_announcements_by_category(): void
    {
        Announcement::factory()->create(['category' => 'academic', 'is_published' => true]);
        Announcement::factory()->create(['category' => 'event', 'is_published' => true]);
        Announcement::factory()->create(['category' => 'academic', 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements?category=academic');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $announcement) {
            $this->assertEquals('academic', $announcement['category']);
        }
    }

    public function test_index_filters_announcements_by_priority(): void
    {
        Announcement::factory()->create(['priority' => 'low', 'is_published' => true]);
        Announcement::factory()->create(['priority' => 'urgent', 'is_published' => true]);
        Announcement::factory()->create(['priority' => 'low', 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements?priority=urgent');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $announcement) {
            $this->assertEquals('urgent', $announcement['priority']);
        }
    }

    public function test_index_searches_announcements(): void
    {
        Announcement::factory()->create([
            'title' => 'Exam Schedule Released',
            'content' => 'Final exams will be held next week',
            'is_published' => true,
        ]);
        Announcement::factory()->create([
            'title' => 'Library Hours',
            'content' => 'The library will be open 24/7 during finals',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements?search=exam');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_filters_announcements_by_course(): void
    {
        Announcement::factory()->create([
            'course_id' => $this->courseModel->id,
            'is_published' => true,
        ]);
        Announcement::factory()->create(['is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements?course_id=' . $this->courseModel->id);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $announcement) {
            $this->assertEquals($this->courseModel->id, $announcement['course_id']);
        }
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/announcements');

        $response->assertStatus(401);
    }

    public function test_index_hides_unpublished_from_students(): void
    {
        Announcement::factory()->create(['title' => 'Published', 'is_published' => true]);
        Announcement::factory()->create(['title' => 'Not Published', 'is_published' => false]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $titles = array_column($data, 'title');
        $this->assertContains('Published', $titles);
        $this->assertNotContains('Not Published', $titles);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating announcements
    // -------------------------------------------------------------------------

    public function test_store_creates_announcement_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $announcementData = [
            'title' => 'Important Notice',
            'content' => 'This is a very important announcement for everyone.',
            'category' => 'general',
            'priority' => 'high',
            'target_audience' => 'everyone',
            'is_published' => true,
            'allow_comments' => true,
        ];

        $response = $this->postJson('/api/announcements', $announcementData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Announcement created successfully',
            ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Important Notice',
            'category' => 'general',
            'priority' => 'high',
        ]);
    }

    public function test_store_creates_announcement_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $announcementData = [
            'title' => 'Class Update',
            'content' => 'Class will be rescheduled.',
            'category' => 'academic',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/announcements', $announcementData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Class Update',
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $announcementData = [
            'title' => 'Student Announcement',
            'content' => 'This should not work.',
        ];

        $response = $this->postJson('/api/announcements', $announcementData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/announcements', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single announcement
    // -------------------------------------------------------------------------

    public function test_show_returns_announcement_for_authenticated_user(): void
    {
        $announcement = Announcement::factory()->create([
            'title' => 'Test Announcement',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/announcements/' . $announcement->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $announcement->id,
                    'title' => 'Test Announcement',
                ],
            ]);
    }

    public function test_show_increments_view_count(): void
    {
        $announcement = Announcement::factory()->create([
            'is_published' => true,
            'view_count' => 10,
        ]);

        Sanctum::actingAs($this->student);

        $this->getJson('/api/announcements/' . $announcement->id);

        $announcement->refresh();
        $this->assertEquals(11, $announcement->view_count);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating announcements
    // -------------------------------------------------------------------------

    public function test_update_modifies_announcement_for_admin(): void
    {
        $announcement = Announcement::factory()->create([
            'title' => 'Original Title',
            'priority' => 'low',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'title' => 'Updated Title',
            'priority' => 'urgent',
        ];

        $response = $this->putJson('/api/announcements/' . $announcement->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Announcement updated successfully',
            ]);

        $announcement->refresh();
        $this->assertEquals('Updated Title', $announcement->title);
        $this->assertEquals('urgent', $announcement->priority);
    }

    public function test_update_fails_for_student(): void
    {
        $announcement = Announcement::factory()->create();

        Sanctum::actingAs($this->student);

        $updateData = [
            'title' => 'Hacked Title',
        ];

        $response = $this->putJson('/api/announcements/' . $announcement->id, $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting announcements
    // -------------------------------------------------------------------------

    public function test_deletes_announcement_for_admin(): void
    {
        $announcement = Announcement::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/announcements/' . $announcement->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $announcement = Announcement::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/announcements/' . $announcement->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUBLISH Tests
    // -------------------------------------------------------------------------

    public function test_publish_announcement_for_admin(): void
    {
        $announcement = Announcement::factory()->create([
            'is_published' => false,
            'published_at' => null,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/announcements/' . $announcement->id . '/publish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Announcement published successfully',
            ]);

        $announcement->refresh();
        $this->assertTrue($announcement->is_published);
        $this->assertNotNull($announcement->published_at);
    }

    // -------------------------------------------------------------------------
    // UNPUBLISH Tests
    // -------------------------------------------------------------------------

    public function test_unpublish_announcement_for_admin(): void
    {
        $announcement = Announcement::factory()->create([
            'is_published' => true,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/announcements/' . $announcement->id . '/unpublish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Announcement unpublished',
            ]);

        $announcement->refresh();
        $this->assertFalse($announcement->is_published);
        $this->assertNull($announcement->published_at);
    }

    // -------------------------------------------------------------------------
    // MARK READ Tests
    // -------------------------------------------------------------------------

    public function test_mark_as_read_for_authenticated_user(): void
    {
        $announcement = Announcement::factory()->create(['is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/announcements/' . $announcement->id . '/mark-read');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Announcement marked as read',
            ]);
    }
}