<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DiscussionPost;
use App\Models\DiscussionThread;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiscussionThreadControllerTest extends TestCase
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
    // INDEX Tests - Listing threads
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_threads_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        DiscussionThread::factory()->count(5)->create();

        $response = $this->getJson('/api/discussion-threads');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_index_filters_threads_by_course(): void
    {
        $otherCourse = Course::factory()->create();

        DiscussionThread::factory()->create(['course_id' => $this->courseModel->id]);
        DiscussionThread::factory()->create(['course_id' => $otherCourse->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads?course_id={$this->courseModel->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertEquals($this->courseModel->id, $thread['course_id']);
        }
    }

    public function test_index_filters_threads_by_module(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->courseModel->id]);
        $otherModule = CourseModule::factory()->create(['course_id' => $this->courseModel->id]);

        DiscussionThread::factory()->create(['module_id' => $module->id]);
        DiscussionThread::factory()->create(['module_id' => $otherModule->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads?module_id={$module->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertEquals($module->id, $thread['module_id']);
        }
    }

    public function test_index_filters_threads_by_type(): void
    {
        DiscussionThread::factory()->create(['type' => 'question']);
        DiscussionThread::factory()->create(['type' => 'discussion']);
        DiscussionThread::factory()->create(['type' => 'question']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?type=question');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertEquals('question', $thread['type']);
        }
    }

    public function test_index_filters_threads_by_status(): void
    {
        DiscussionThread::factory()->create(['status' => 'open']);
        DiscussionThread::factory()->create(['status' => 'closed']);
        DiscussionThread::factory()->create(['status' => 'open']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?status=open');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertEquals('open', $thread['status']);
        }
    }

    public function test_index_filters_threads_by_pinned_status(): void
    {
        DiscussionThread::factory()->create(['is_pinned' => true]);
        DiscussionThread::factory()->create(['is_pinned' => false]);
        DiscussionThread::factory()->create(['is_pinned' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?is_pinned=true');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertTrue($thread['is_pinned']);
        }
    }

    public function test_index_filters_threads_by_locked_status(): void
    {
        DiscussionThread::factory()->create(['is_locked' => true]);
        DiscussionThread::factory()->create(['is_locked' => false]);
        DiscussionThread::factory()->create(['is_locked' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?is_locked=true');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $thread) {
            $this->assertTrue($thread['is_locked']);
        }
    }

    public function test_index_searches_threads_by_title(): void
    {
        DiscussionThread::factory()->create(['title' => 'Question about homework']);
        DiscussionThread::factory()->create(['title' => 'Discussion on chapter 5']);
        DiscussionThread::factory()->create(['title' => 'Help with assignment']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?search=homework');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_searches_threads_by_content(): void
    {
        DiscussionThread::factory()->create([
            'title' => 'Thread 1',
            'content' => 'Need help with calculus integration',
        ]);
        DiscussionThread::factory()->create([
            'title' => 'Thread 2',
            'content' => 'Discussion about derivatives',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?search=calculus');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_sorts_threads_by_recent(): void
    {
        $thread1 = DiscussionThread::factory()->create([
            'last_post_at' => now()->subDays(2),
        ]);
        $thread2 = DiscussionThread::factory()->create([
            'last_post_at' => now()->subHours(1),
        ]);
        $thread3 = DiscussionThread::factory()->create([
            'last_post_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?sort=recent');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($thread3->id, $data[0]['id']);
    }

    public function test_index_sorts_threads_by_popular(): void
    {
        $thread1 = DiscussionThread::factory()->create(['view_count' => 10]);
        $thread2 = DiscussionThread::factory()->create(['view_count' => 100]);
        $thread3 = DiscussionThread::factory()->create(['view_count' => 50]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?sort=popular');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($thread2->id, $data[0]['id']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/discussion-threads');

        $response->assertStatus(401);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        DiscussionThread::factory()->count(20)->create();

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 5,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating threads
    // -------------------------------------------------------------------------

    public function test_store_creates_thread_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $threadData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Question about chapter 3',
            'content' => 'Can someone explain the concept of polymorphism?',
            'type' => 'question',
        ];

        $response = $this->postJson('/api/discussion-threads', $threadData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Discussion thread created successfully',
            ]);

        $this->assertDatabaseHas('discussion_threads', [
            'title' => 'Question about chapter 3',
            'created_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('discussion_posts', [
            'content' => 'Can someone explain the concept of polymorphism?',
        ]);
    }

    public function test_store_creates_thread_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $threadData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Announcement about exam',
            'content' => 'The midterm exam will be held next week.',
            'type' => 'announcement',
        ];

        $response = $this->postJson('/api/discussion-threads', $threadData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Discussion thread created successfully',
            ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $threadData = [
            'course_id' => $this->courseModel->id,
            'title' => 'My question',
            'content' => 'I have a question',
        ];

        $response = $this->postJson('/api/discussion-threads', $threadData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussion-threads', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id', 'title', 'content']);
    }

    public function test_store_creates_initial_post(): void
    {
        Sanctum::actingAs($this->admin);

        $threadData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Test Thread',
            'content' => 'This is the initial post content',
            'type' => 'discussion',
        ];

        $response = $this->postJson('/api/discussion-threads', $threadData);

        $response->assertStatus(201);

        $thread = DiscussionThread::where('title', 'Test Thread')->first();
        $this->assertEquals(1, $thread->reply_count);
        $this->assertEquals($this->admin->id, $thread->last_post_by);
    }

    public function test_store_sets_default_values(): void
    {
        Sanctum::actingAs($this->admin);

        $threadData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Test Thread',
            'content' => 'Content',
        ];

        $response = $this->postJson('/api/discussion-threads', $threadData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('discussion_threads', [
            'title' => 'Test Thread',
            'status' => 'open',
            'is_pinned' => false,
            'is_locked' => false,
            'view_count' => 0,
            'reply_count' => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single thread
    // -------------------------------------------------------------------------

    public function test_show_returns_thread_for_authenticated_user(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $thread->id,
                ],
            ]);
    }

    public function test_show_increments_view_count(): void
    {
        $thread = DiscussionThread::factory()->create(['view_count' => 5]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(200);

        $thread->refresh();
        $this->assertEquals(6, $thread->view_count);
    }

    public function test_show_returns_404_for_nonexistent_thread(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-threads/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $thread = DiscussionThread::factory()->create();

        $response = $this->getJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating threads
    // -------------------------------------------------------------------------

    public function test_update_modifies_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Updated Thread Title',
            'type' => 'discussion',
            'is_pinned' => true,
        ];

        $response = $this->putJson("/api/discussion-threads/{$thread->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Discussion thread updated successfully',
            ]);

        $this->assertDatabaseHas('discussion_threads', [
            'id' => $thread->id,
            'title' => 'Updated Thread Title',
        ]);
    }

    public function test_update_modifies_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Updated by Admin',
        ];

        $response = $this->putJson("/api/discussion-threads/{$thread->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_update_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'faculty']);
        $thread = DiscussionThread::factory()->create(['created_by' => $otherUser->id]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/discussion-threads/{$thread->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only edit your own threads',
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'course_id' => $this->courseModel->id,
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/discussion-threads/{$thread->id}", $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting threads
    // -------------------------------------------------------------------------

    public function test_delete_removes_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('discussion_threads', ['id' => $thread->id]);
    }

    public function test_delete_removes_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(204);
    }

    public function test_delete_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'faculty']);
        $thread = DiscussionThread::factory()->create(['created_by' => $otherUser->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only delete your own threads',
            ]);
    }

    public function test_delete_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create(['created_by' => $this->faculty->id]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/discussion-threads/{$thread->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // POSTS Tests - Getting thread posts
    // -------------------------------------------------------------------------

    public function test_posts_returns_thread_posts(): void
    {
        $thread = DiscussionThread::factory()->create();
        DiscussionPost::factory()->count(3)->create(['thread_id' => $thread->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/{$thread->id}/posts");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread posts retrieved successfully',
            ]);
    }

    public function test_posts_returns_top_level_posts_by_default(): void
    {
        $thread = DiscussionThread::factory()->create();
        $post1 = DiscussionPost::factory()->create(['thread_id' => $thread->id, 'parent_id' => null]);
        $post2 = DiscussionPost::factory()->create(['thread_id' => $thread->id, 'parent_id' => $post1->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/{$thread->id}/posts");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_posts_includes_replies_when_requested(): void
    {
        $thread = DiscussionThread::factory()->create();
        $post1 = DiscussionPost::factory()->create(['thread_id' => $thread->id, 'parent_id' => null]);
        DiscussionPost::factory()->count(2)->create(['thread_id' => $thread->id, 'parent_id' => $post1->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/{$thread->id}/posts?include_replies=true");

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // PIN Tests
    // -------------------------------------------------------------------------

    public function test_pin_pins_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['is_pinned' => false]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/pin");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread pinned successfully',
            ]);

        $thread->refresh();
        $this->assertTrue($thread->is_pinned);
    }

    public function test_pin_pins_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['is_pinned' => false]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/pin");

        $response->assertStatus(200);
    }

    public function test_pin_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/pin");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // UNPIN Tests
    // -------------------------------------------------------------------------

    public function test_unpin_unpins_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['is_pinned' => true]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unpin");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread unpinned successfully',
            ]);

        $thread->refresh();
        $this->assertFalse($thread->is_pinned);
    }

    public function test_unpin_unpins_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['is_pinned' => true]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unpin");

        $response->assertStatus(200);
    }

    public function test_unpin_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unpin");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // LOCK Tests
    // -------------------------------------------------------------------------

    public function test_lock_locks_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['is_locked' => false]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/lock");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread locked successfully',
            ]);

        $thread->refresh();
        $this->assertTrue($thread->is_locked);
        $this->assertEquals($this->admin->id, $thread->locked_by);
    }

    public function test_lock_locks_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['is_locked' => false]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/lock");

        $response->assertStatus(200);
    }

    public function test_lock_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/lock");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // UNLOCK Tests
    // -------------------------------------------------------------------------

    public function test_unlock_unlocks_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'is_locked' => true,
            'locked_by' => $this->admin->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unlock");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread unlocked successfully',
            ]);

        $thread->refresh();
        $this->assertFalse($thread->is_locked);
        $this->assertNull($thread->locked_by);
    }

    public function test_unlock_unlocks_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['is_locked' => true]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unlock");

        $response->assertStatus(200);
    }

    public function test_unlock_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/unlock");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // CLOSE Tests
    // -------------------------------------------------------------------------

    public function test_close_closes_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'created_by' => $this->faculty->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/close");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread closed successfully',
            ]);

        $thread->refresh();
        $this->assertEquals('closed', $thread->status);
        $this->assertEquals($this->faculty->id, $thread->closed_by);
    }

    public function test_close_closes_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'open']);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/close");

        $response->assertStatus(200);
    }

    public function test_close_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'faculty']);
        $thread = DiscussionThread::factory()->create([
            'created_by' => $otherUser->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/close");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only close your own threads',
            ]);
    }

    // -------------------------------------------------------------------------
    // REOPEN Tests
    // -------------------------------------------------------------------------

    public function test_reopen_reopens_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'created_by' => $this->faculty->id,
            'status' => 'closed',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/reopen");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread reopened successfully',
            ]);

        $thread->refresh();
        $this->assertEquals('open', $thread->status);
    }

    public function test_reopen_reopens_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'closed']);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/reopen");

        $response->assertStatus(200);
    }

    public function test_reopen_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'faculty']);
        $thread = DiscussionThread::factory()->create([
            'created_by' => $otherUser->id,
            'status' => 'closed',
        ]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/reopen");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // ARCHIVE Tests
    // -------------------------------------------------------------------------

    public function test_archive_archives_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'open']);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/archive");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread archived successfully',
            ]);

        $thread->refresh();
        $this->assertEquals('archived', $thread->status);
    }

    public function test_archive_archives_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'open']);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/archive");

        $response->assertStatus(200);
    }

    public function test_archive_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/archive");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // RESTORE Tests
    // -------------------------------------------------------------------------

    public function test_restore_restores_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'archived']);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread restored successfully',
            ]);

        $thread->refresh();
        $this->assertEquals('open', $thread->status);
    }

    public function test_restore_restores_thread_for_faculty(): void
    {
        $thread = DiscussionThread::factory()->create(['status' => 'archived']);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/restore");

        $response->assertStatus(200);
    }

    public function test_restore_fails_for_student(): void
    {
        $thread = DiscussionThread::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$thread->id}/restore");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MY THREADS Tests
    // -------------------------------------------------------------------------

    public function test_my_threads_returns_user_threads(): void
    {
        Sanctum::actingAs($this->faculty);

        DiscussionThread::factory()->count(3)->create(['created_by' => $this->faculty->id]);
        DiscussionThread::factory()->count(2)->create(['created_by' => $this->student->id]);

        $response = $this->getJson('/api/discussion-threads/my-threads');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your threads retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    // -------------------------------------------------------------------------
    // BY COURSE Tests
    // -------------------------------------------------------------------------

    public function test_by_course_returns_course_threads(): void
    {
        DiscussionThread::factory()->count(3)->create(['course_id' => $this->courseModel->id]);
        $otherCourse = Course::factory()->create();
        DiscussionThread::factory()->count(2)->create(['course_id' => $otherCourse->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/by-course/{$this->courseModel->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course threads retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    // -------------------------------------------------------------------------
    // BY MODULE Tests
    // -------------------------------------------------------------------------

    public function test_by_module_returns_module_threads(): void
    {
        $module = CourseModule::factory()->create(['course_id' => $this->courseModel->id]);

        DiscussionThread::factory()->count(3)->create(['module_id' => $module->id]);
        $otherModule = CourseModule::factory()->create(['course_id' => $this->courseModel->id]);
        DiscussionThread::factory()->count(2)->create(['module_id' => $otherModule->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-threads/by-module/{$module->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Module threads retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }
}