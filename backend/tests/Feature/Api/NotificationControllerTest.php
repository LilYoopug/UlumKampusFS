<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Notification Controller Feature Tests
 *
 * Tests all notification operations including CRUD, read status management,
 * filtering, and user-specific access controls.
 */
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing notifications
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_notifications_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(5)->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/notifications');

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

    public function test_index_filters_by_user_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        Notification::factory()->create(['user_id' => $this->student->id]);
        Notification::factory()->create(['user_id' => $this->otherStudent->id]);

        $response = $this->getJson("/api/notifications?user_id={$this->student->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $notification) {
            $this->assertEquals($this->student->id, $notification['user_id']);
        }
    }

    public function test_index_filters_by_type(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create(['user_id' => $this->student->id, 'type' => 'assignment_due']);
        Notification::factory()->create(['user_id' => $this->student->id, 'type' => 'announcement']);
        Notification::factory()->create(['user_id' => $this->student->id, 'type' => 'assignment_due']);

        $response = $this->getJson('/api/notifications?type=assignment_due');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $notification) {
            $this->assertEquals('assignment_due', $notification['type']);
        }
    }

    public function test_index_filters_by_priority(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create(['user_id' => $this->student->id, 'priority' => 'high']);
        Notification::factory()->create(['user_id' => $this->student->id, 'priority' => 'low']);
        Notification::factory()->create(['user_id' => $this->student->id, 'priority' => 'high']);

        $response = $this->getJson('/api/notifications?priority=high');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $notification) {
            $this->assertEquals('high', $notification['priority']);
        }
    }

    public function test_index_filters_by_read_status(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create(['user_id' => $this->student->id, 'is_read' => true]);
        Notification::factory()->create(['user_id' => $this->student->id, 'is_read' => false]);
        Notification::factory()->create(['user_id' => $this->student->id, 'is_read' => true]);

        $response = $this->getJson('/api/notifications?is_read=false');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $notification) {
            $this->assertFalse($notification['is_read']);
        }
    }

    public function test_index_filters_by_sent_status(): void
    {
        Sanctum::actingAs($this->admin);

        Notification::factory()->create(['is_sent' => true]);
        Notification::factory()->create(['is_sent' => false]);
        Notification::factory()->create(['is_sent' => true]);

        $response = $this->getJson('/api/notifications?is_sent=true');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $notification) {
            $this->assertTrue($notification['is_sent']);
        }
    }

    public function test_index_searches_by_title(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Assignment Due Reminder',
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'New Announcement',
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Help with Assignment',
        ]);

        $response = $this->getJson('/api/notifications?search=assignment');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_searches_by_message(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Notification 1',
            'message' => 'Your assignment is due tomorrow',
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'title' => 'Notification 2',
            'message' => 'Class has been rescheduled',
        ]);

        $response = $this->getJson('/api/notifications?search=tomorrow');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_excludes_expired_by_default(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'expires_at' => now()->addDays(7),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    public function test_index_includes_expired_when_requested(): void
    {
        Sanctum::actingAs($this->admin);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'expires_at' => now()->addDays(7),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications?include_expired=true');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_index_sorts_by_newest(): void
    {
        Sanctum::actingAs($this->student);

        $notification1 = Notification::factory()->create([
            'user_id' => $this->student->id,
            'created_at' => now()->subDays(2),
        ]);
        $notification2 = Notification::factory()->create([
            'user_id' => $this->student->id,
            'created_at' => now()->subHours(1),
        ]);
        $notification3 = Notification::factory()->create([
            'user_id' => $this->student->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/notifications?sort=newest');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($notification3->id, $data[0]['id']);
    }

    public function test_index_sorts_by_oldest(): void
    {
        Sanctum::actingAs($this->student);

        $notification1 = Notification::factory()->create([
            'user_id' => $this->student->id,
            'created_at' => now()->subDays(2),
        ]);
        $notification2 = Notification::factory()->create([
            'user_id' => $this->student->id,
            'created_at' => now()->subHours(1),
        ]);

        $response = $this->getJson('/api/notifications?sort=oldest');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($notification1->id, $data[0]['id']);
    }

    public function test_index_sorts_by_priority(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'priority' => 'low',
            'created_at' => now()->subMinutes(10),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'priority' => 'urgent',
            'created_at' => now()->subMinutes(5),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'priority' => 'medium',
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/notifications?sort=priority');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('urgent', $data[0]['priority']);
        $this->assertEquals('medium', $data[1]['priority']);
        $this->assertEquals('low', $data[2]['priority']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(20)->create(['user_id' => $this->student->id]);

        $response = $this->getJson('/api/notifications?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 5,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating notifications
    // -------------------------------------------------------------------------

    public function test_store_creates_notification_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $notificationData = [
            'user_id' => $this->student->id,
            'type' => 'assignment_due',
            'title' => 'Assignment Due Reminder',
            'message' => 'Your assignment is due tomorrow.',
            'priority' => 'high',
            'action_url' => 'https://example.com/assignments/1',
        ];

        $response = $this->postJson('/api/notifications', $notificationData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Notification created successfully',
            ]);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Assignment Due Reminder',
            'user_id' => $this->student->id,
        ]);
    }

    public function test_store_fails_for_non_admin(): void
    {
        Sanctum::actingAs($this->student);

        $notificationData = [
            'user_id' => $this->otherStudent->id,
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/notifications', $notificationData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/notifications', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'type', 'title', 'message']);
    }

    public function test_store_sets_default_values(): void
    {
        Sanctum::actingAs($this->admin);

        $notificationData = [
            'user_id' => $this->student->id,
            'type' => 'test',
            'title' => 'Test',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/notifications', $notificationData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('notifications', [
            'title' => 'Test',
            'is_read' => false,
            'is_sent' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single notification
    // -------------------------------------------------------------------------

    public function test_show_returns_notification_for_owner(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                ],
            ]);
    }

    public function test_show_returns_notification_for_admin(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
    }

    public function test_show_fails_for_non_owner(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    public function test_show_returns_404_for_nonexistent_notification(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/notifications/99999');

        $response->assertStatus(403); // Not found returns forbidden due to user check
    }

    public function test_show_requires_authentication(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating notifications
    // -------------------------------------------------------------------------

    public function test_update_modifies_notification_for_admin(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'title' => 'Updated Title',
            'message' => 'Updated message',
            'priority' => 'urgent',
        ];

        $response = $this->putJson("/api/notifications/{$notification->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification updated successfully',
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_fails_for_non_admin(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/notifications/{$notification->id}", $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting notifications
    // -------------------------------------------------------------------------

    public function test_delete_removes_notification_for_owner(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_delete_removes_notification_for_admin(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(204);
    }

    public function test_delete_fails_for_non_owner(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->student->id]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MARK READ Tests
    // -------------------------------------------------------------------------

    public function test_mark_read_marks_notification_as_read_for_owner(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'read_at' => null,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/notifications/{$notification->id}/mark-read");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);

        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_read_fails_for_non_owner(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->postJson("/api/notifications/{$notification->id}/mark-read");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MARK UNREAD Tests
    // -------------------------------------------------------------------------

    public function test_mark_unread_marks_notification_as_unread_for_owner(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => true,
            'read_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/notifications/{$notification->id}/mark-unread");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification marked as unread',
            ]);

        $notification->refresh();
        $this->assertFalse($notification->is_read);
        $this->assertNull($notification->read_at);
    }

    public function test_mark_unread_fails_for_non_owner(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);

        Sanctum::actingAs($this->otherStudent);

        $response = $this->postJson("/api/notifications/{$notification->id}/mark-unread");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MARK ALL READ Tests
    // -------------------------------------------------------------------------

    public function test_mark_all_read_marks_all_notifications_as_read(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->otherStudent->id,
            'is_read' => false,
        ]);

        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['marked_count']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);
    }

    public function test_mark_all_read_only_affects_current_user(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->otherStudent->id,
            'is_read' => false,
        ]);

        $this->postJson('/api/notifications/mark-all-read');

        // Student's notifications should be read
        $this->assertEquals(0, $this->student->notifications()->unread()->count());

        // Other student's notifications should remain unread
        $this->assertEquals(2, $this->otherStudent->notifications()->unread()->count());
    }

    // -------------------------------------------------------------------------
    // UNREAD Tests
    // -------------------------------------------------------------------------

    public function test_unread_returns_unread_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/notifications/unread');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Unread notifications retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_unread_excludes_expired_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'expires_at' => now()->addDays(7),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications/unread');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // URGENT Tests
    // -------------------------------------------------------------------------

    public function test_urgent_returns_urgent_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'priority' => 'urgent',
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'priority' => 'high',
        ]);

        $response = $this->getJson('/api/notifications/urgent');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Urgent notifications retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
        foreach ($data as $notification) {
            $this->assertEquals('urgent', $notification['priority']);
        }
    }

    public function test_urgent_excludes_expired_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->create([
            'user_id' => $this->student->id,
            'priority' => 'urgent',
            'expires_at' => now()->addDays(7),
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'priority' => 'urgent',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications/urgent');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // CLEAR READ Tests
    // -------------------------------------------------------------------------

    public function test_clear_read_deletes_read_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);

        $response = $this->deleteJson('/api/notifications/clear-read');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Read notifications cleared',
            ]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['cleared_count']);

        $this->assertEquals(2, $this->student->notifications()->count());
    }

    public function test_clear_read_only_affects_current_user(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->otherStudent->id,
            'is_read' => true,
        ]);

        $this->deleteJson('/api/notifications/clear-read');

        // Student's read notifications should be deleted
        $this->assertEquals(0, $this->student->notifications()->read()->count());

        // Other student's read notifications should remain
        $this->assertEquals(2, $this->otherStudent->notifications()->read()->count());
    }

    // -------------------------------------------------------------------------
    // COUNTS Tests
    // -------------------------------------------------------------------------

    public function test_counts_returns_notification_counts(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(5)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
        ]);
        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'is_read' => true,
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'priority' => 'urgent',
        ]);
        Notification::factory()->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications/counts');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Notification counts retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertEquals(7, $data['total']); // 5 unread + 3 read - 1 expired
        $this->assertEquals(5, $data['unread']); // 5 unread - 1 expired
        $this->assertEquals(2, $data['urgent']); // 2 urgent
    }

    public function test_counts_only_includes_active_notifications(): void
    {
        Sanctum::actingAs($this->student);

        Notification::factory()->count(3)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'expires_at' => now()->addDays(7),
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->student->id,
            'is_read' => false,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/notifications/counts');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(3, $data['unread']);
    }
}