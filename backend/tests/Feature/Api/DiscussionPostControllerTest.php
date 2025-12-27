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

class DiscussionPostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $courseModel;
    protected DiscussionThread $threadModel;

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
        $this->threadModel = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'created_by' => $this->faculty->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating posts
    // -------------------------------------------------------------------------

    public function test_store_creates_post_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This is a helpful response to the thread.',
        ];

        $response = $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", $postData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Post created successfully',
            ]);

        $this->assertDatabaseHas('discussion_posts', [
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
            'content' => 'This is a helpful response to the thread.',
        ]);
    }

    public function test_store_creates_reply_to_parent_post(): void
    {
        Sanctum::actingAs($this->student);

        $parentPost = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        $postData = [
            'content' => 'This is a reply to the parent post.',
            'parent_id' => $parentPost->id,
        ];

        $response = $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", $postData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('discussion_posts', [
            'thread_id' => $this->threadModel->id,
            'parent_id' => $parentPost->id,
            'user_id' => $this->student->id,
        ]);
    }

    public function test_store_fails_for_unauthenticated_user(): void
    {
        $postData = [
            'content' => 'This should fail.',
        ];

        $response = $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", $postData);

        $response->assertStatus(401);
    }

    public function test_store_validates_required_content(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_store_validates_content_is_not_empty(): void
    {
        Sanctum::actingAs($this->student);

        $postData = [
            'content' => '   ',
        ];

        $response = $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", $postData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_store_fails_for_nonexistent_thread(): void
    {
        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This should fail.',
        ];

        $response = $this->postJson('/api/discussion-threads/99999/posts', $postData);

        $response->assertStatus(404);
    }

    public function test_store_fails_for_locked_thread(): void
    {
        $lockedThread = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'is_locked' => true,
        ]);

        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This should fail.',
        ];

        $response = $this->postJson("/api/discussion-threads/{$lockedThread->id}/posts", $postData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot post to a locked thread',
            ]);
    }

    public function test_store_fails_for_closed_thread(): void
    {
        $closedThread = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'status' => 'closed',
        ]);

        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This should fail.',
        ];

        $response = $this->postJson("/api/discussion-threads/{$closedThread->id}/posts", $postData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot post to a closed thread',
            ]);
    }

    public function test_store_updates_thread_last_post_info(): void
    {
        Sanctum::actingAs($this->student);

        $postData = [
            'content' => 'This is a new post.',
        ];

        $this->postJson("/api/discussion-threads/{$this->threadModel->id}/posts", $postData);

        $this->threadModel->refresh();
        $this->assertEquals($this->student->id, $this->threadModel->last_post_by);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single post
    // -------------------------------------------------------------------------

    public function test_show_returns_post_for_authenticated_user(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $post->id,
                ],
            ]);
    }

    public function test_show_includes_user_relationship(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_post(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/discussion-posts/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        $response = $this->getJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating posts
    // -------------------------------------------------------------------------

    public function test_update_modifies_post_for_owner(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
            'content' => 'Original content',
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content with new information.',
        ];

        $response = $this->putJson("/api/discussion-posts/{$post->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully',
            ]);

        $this->assertDatabaseHas('discussion_posts', [
            'id' => $post->id,
            'content' => 'Updated content with new information.',
            'is_edited' => true,
        ]);
    }

    public function test_update_modifies_post_for_admin(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'content' => 'Updated by admin.',
        ];

        $response = $this->putJson("/api/discussion-posts/{$post->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_update_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'student']);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'This should fail.',
        ];

        $response = $this->putJson("/api/discussion-posts/{$post->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only edit your own posts',
            ]);
    }

    public function test_update_sets_edited_flags(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'content' => 'Updated content.',
        ];

        $this->putJson("/api/discussion-posts/{$post->id}", $updateData);

        $post->refresh();
        $this->assertTrue($post->is_edited);
        $this->assertEquals($this->student->id, $post->edited_by);
        $this->assertNotNull($post->edited_at);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting posts
    // -------------------------------------------------------------------------

    public function test_delete_removes_post_for_owner(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('discussion_posts', ['id' => $post->id]);
    }

    public function test_delete_removes_post_for_admin(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->student->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(204);
    }

    public function test_delete_fails_for_non_owner(): void
    {
        $otherUser = User::factory()->create(['role' => 'student']);
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/discussion-posts/{$post->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You can only delete your own posts',
            ]);
    }

    // -------------------------------------------------------------------------
    // MARK AS SOLUTION Tests
    // -------------------------------------------------------------------------

    public function test_mark_as_solution_marks_post_by_thread_creator(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'created_by' => $this->student->id,
        ]);

        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/mark-as-solution");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post marked as solution successfully',
            ]);

        $post->refresh();
        $this->assertTrue($post->is_solution);
        $this->assertEquals($this->student->id, $post->marked_as_solution_by);
    }

    public function test_mark_as_solution_works_for_admin(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/mark-as-solution");

        $response->assertStatus(200);

        $post->refresh();
        $this->assertTrue($post->is_solution);
    }

    public function test_mark_as_solution_unmarks_other_solutions(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'created_by' => $this->student->id,
        ]);

        $existingSolution = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->faculty->id,
            'is_solution' => true,
        ]);

        $newSolution = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/discussion-posts/{$newSolution->id}/mark-as-solution");

        $existingSolution->refresh();
        $newSolution->refresh();

        $this->assertFalse($existingSolution->is_solution);
        $this->assertTrue($newSolution->is_solution);
    }

    public function test_mark_as_solution_fails_for_non_thread_creator(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/mark-as-solution");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only the thread creator can mark posts as solutions',
            ]);
    }

    public function test_unmark_as_solution_works_for_thread_creator(): void
    {
        $thread = DiscussionThread::factory()->create([
            'course_id' => $this->courseModel->id,
            'created_by' => $this->student->id,
        ]);

        $post = DiscussionPost::factory()->create([
            'thread_id' => $thread->id,
            'user_id' => $this->faculty->id,
            'is_solution' => true,
            'marked_as_solution_by' => $this->student->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/unmark-as-solution");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post unmarked as solution successfully',
            ]);

        $post->refresh();
        $this->assertFalse($post->is_solution);
    }

    // -------------------------------------------------------------------------
    // LIKE Tests
    // -------------------------------------------------------------------------

    public function test_like_increments_likes_count(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
            'likes_count' => 5,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post liked successfully',
            ]);

        $post->refresh();
        $this->assertEquals(6, $post->likes_count);
    }

    public function test_unlike_decrements_likes_count(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
            'likes_count' => 5,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/discussion-posts/{$post->id}/unlike");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post unliked successfully',
            ]);

        $post->refresh();
        $this->assertEquals(4, $post->likes_count);
    }

    public function test_like_prevents_negative_likes_count(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
            'likes_count' => 0,
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/discussion-posts/{$post->id}/unlike");

        $post->refresh();
        $this->assertGreaterThanOrEqual(0, $post->likes_count);
    }

    // -------------------------------------------------------------------------
    // REPLIES Tests
    // -------------------------------------------------------------------------

    public function test_replies_returns_post_replies(): void
    {
        $post = DiscussionPost::factory()->create([
            'thread_id' => $this->threadModel->id,
            'user_id' => $this->faculty->id,
        ]);

        DiscussionPost::factory()->count(3)->create([
            'thread_id' => $this->threadModel->id,
            'parent_id' => $post->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/discussion-posts/{$post->id}/replies");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Post replies retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }
}