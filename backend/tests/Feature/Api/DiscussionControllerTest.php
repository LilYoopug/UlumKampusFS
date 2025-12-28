<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DiscussionThread;
use App\Models\DiscussionPost;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiscussionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $otherStudent;
    protected Course $course;
    protected CourseModule $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);

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
    // INDEX Tests - Listing discussions
    // -------------------------------------------------------------------------

    public function test_index_returns_open_threads_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        DiscussionThread::factory()->count(3)->create([
            'course_id' => $this->course->id,
            'status' => 'open',
        ]);
        DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'status' => 'closed',
        ]);

        $response = $this->getJson('/api/discussions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/discussions');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // THREADS Tests - Getting all threads
    // -------------------------------------------------------------------------

    public function test_threads_returns_all_threads_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        DiscussionThread::factory()->count(2)->create(['course_id' => $this->course->id]);

        $response = $this->getJson('/api/discussions/threads');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    // -------------------------------------------------------------------------
    // SHOW THREAD Tests - Retrieving a single thread
    // -------------------------------------------------------------------------

    public function test_show_thread_increments_view_count(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'view_count' => 5,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussions/threads/' . $thread->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $thread->id,
                ],
            ]);

        $thread->refresh();
        $this->assertEquals(6, $thread->view_count);
    }

    public function test_show_thread_returns_404_for_nonexistent_thread(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussions/threads/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // POSTS Tests - Getting posts for a thread
    // -------------------------------------------------------------------------

    public function test_posts_returns_thread_posts(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        DiscussionPost::factory()->count(3)->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussions/threads/' . $thread->id . '/posts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    // -------------------------------------------------------------------------
    // STORE THREAD Tests - Creating a new thread
    // -------------------------------------------------------------------------

    public function test_store_thread_creates_thread_with_first_post(): void
    {
        Sanctum::actingAs($this->student);

        $threadData = [
            'course_id' => $this->course->id,
            'module_id' => $this->module->id,
            'title' => 'How to solve this problem?',
            'content' => 'I need help understanding this concept',
            'type' => 'question',
        ];

        $response = $this->postJson('/api/discussions/threads', $threadData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Discussion thread created successfully',
                'data' => [
                    'title' => 'How to solve this problem?',
                ],
            ]);

        $this->assertDatabaseHas('discussion_threads', [
            'title' => 'How to solve this problem?',
            'created_by' => $this->student->id,
            'reply_count' => 1,
        ]);

        $this->assertDatabaseHas('discussion_posts', [
            'thread_id' => $response->json('data.id'),
            'user_id' => $this->student->id,
            'content' => 'I need help understanding this concept',
        ]);
    }

    public function test_store_thread_validates_required_fields(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/discussions/threads', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['course_id', 'title', 'content']);
    }

    public function test_store_thread_requires_authentication(): void
    {
        $response = $this->postJson('/api/discussions/threads', []);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE POST Tests - Creating a new post in a thread
    // -------------------------------------------------------------------------

    public function test_store_post_creates_reply(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'reply_count' => 1,
        ]);

        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This is my answer to the question',
        ];

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/posts', $postData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Post created successfully',
            ]);

        $thread->refresh();
        $this->assertEquals(2, $thread->reply_count);
        $this->assertEquals($this->student->id, $thread->last_post_by);
    }

    public function test_store_post_creates_nested_reply(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $parentPost = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $postData = [
            'content' => 'Reply to the parent post',
            'parent_id' => $parentPost->id,
        ];

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/posts', $postData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('discussion_posts', [
            'parent_id' => $parentPost->id,
            'user_id' => $this->otherStudent->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // UPDATE THREAD Tests - Updating a thread
    // -------------------------------------------------------------------------

    public function test_update_thread_modifies_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
            'title' => 'Original Title',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ];

        $response = $this->putJson('/api/discussions/threads/' . $thread->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread updated successfully',
                'data' => [
                    'title' => 'Updated Title',
                ],
            ]);
    }

    public function test_update_thread_fails_for_non_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $updateData = [
            'title' => 'Hacked Title',
        ];

        $response = $this->putJson('/api/discussions/threads/' . $thread->id, $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only edit your own threads',
            ]);
    }

    // -------------------------------------------------------------------------
    // UPDATE POST Tests - Updating a post
    // -------------------------------------------------------------------------

    public function test_update_post_modifies_post_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
            'content' => 'Original content',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->putJson('/api/discussions/posts/' . $post->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => [
                    'content' => 'Updated content',
                    'is_edited' => true,
                ],
            ]);

        $post->refresh();
        $this->assertTrue($post->is_edited);
        $this->assertNotNull($post->edited_at);
    }

    public function test_update_post_fails_for_non_owner(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $updateData = [
            'content' => 'Hacked content',
        ];

        $response = $this->putJson('/api/discussions/posts/' . $post->id, $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only edit your own posts',
            ]);
    }

    // -------------------------------------------------------------------------
    // DELETE THREAD Tests - Deleting a thread
    // -------------------------------------------------------------------------

    public function test_delete_thread_removes_thread_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/discussions/threads/' . $thread->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('discussion_threads', ['id' => $thread->id]);
    }

    public function test_delete_thread_fails_for_non_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->student->id,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->deleteJson('/api/discussions/threads/' . $thread->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only delete your own threads',
            ]);
    }

    // -------------------------------------------------------------------------
    // DELETE POST Tests - Deleting a post
    // -------------------------------------------------------------------------

    public function test_delete_post_removes_post_for_owner(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'reply_count' => 2,
        ]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/discussions/posts/' . $post->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('discussion_posts', ['id' => $post->id]);
    }

    public function test_delete_post_updates_thread_reply_count(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'reply_count' => 3,
        ]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $this->deleteJson('/api/discussions/posts/' . $post->id);

        $thread->refresh();
        $this->assertEquals(2, $thread->reply_count);
    }

    // -------------------------------------------------------------------------
    // LIKE POST Tests - Liking a post
    // -------------------------------------------------------------------------

    public function test_like_post_increments_like_count(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
            'likes_count' => 5,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/like');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post liked',
            ]);

        $post->refresh();
        $this->assertEquals(6, $post->likes_count);
    }

    // -------------------------------------------------------------------------
    // PIN THREAD Tests - Pinning a thread
    // -------------------------------------------------------------------------

    public function test_pin_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'is_pinned' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/pin');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread pinned',
            ]);

        $thread->refresh();
        $this->assertTrue($thread->is_pinned);
    }

    public function test_unpin_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'is_pinned' => true,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/unpin');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread unpinned',
            ]);

        $thread->refresh();
        $this->assertFalse($thread->is_pinned);
    }

    // -------------------------------------------------------------------------
    // LOCK THREAD Tests - Locking a thread
    // -------------------------------------------------------------------------

    public function test_lock_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'is_locked' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/lock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread locked',
            ]);

        $thread->refresh();
        $this->assertTrue($thread->is_locked);
    }

    public function test_unlock_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'is_locked' => true,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/unlock');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread unlocked',
            ]);

        $thread->refresh();
        $this->assertFalse($thread->is_locked);
    }

    // -------------------------------------------------------------------------
    // CLOSE THREAD Tests - Closing a thread
    // -------------------------------------------------------------------------

    public function test_close_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/close');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread closed',
            ]);

        $thread->refresh();
        $this->assertEquals('closed', $thread->status);
    }

    public function test_reopen_thread_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->course->id,
            'status' => 'closed',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/threads/' . $thread->id . '/reopen');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thread reopened',
            ]);

        $thread->refresh();
        $this->assertEquals('open', $thread->status);
    }

    // -------------------------------------------------------------------------
    // MARK SOLUTION Tests - Marking a post as solution
    // -------------------------------------------------------------------------

    public function test_mark_solution_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $post1 = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
            'is_solution' => true,
        ]);
        $post2 = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->otherStudent->id,
            'is_solution' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/posts/' . $post2->id . '/mark-solution');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post marked as solution',
            ]);

        $post1->refresh();
        $post2->refresh();
        $this->assertFalse($post1->is_solution);
        $this->assertTrue($post2->is_solution);
    }

    public function test_unmark_solution_for_admin(): void
    {
        $thread = DiscussionThread::factory()->create(['course_id' => $this->course->id]);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->student->id,
            'is_solution' => true,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/discussions/posts/' . $post->id . '/unmark-solution');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post unmarked as solution',
            ]);

        $post->refresh();
        $this->assertFalse($post->is_solution);
    }
}