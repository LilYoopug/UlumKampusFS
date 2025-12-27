<?php

namespace Tests\Feature\Api;

use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        // Create faculty and major for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing users
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_users_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

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

    public function test_index_filters_users_by_role(): void
    {
        User::factory()->create(['role' => 'student']);
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users?role=student');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $user) {
            $this->assertEquals('student', $user['role']);
        }
    }

    public function test_index_searches_users_by_name(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);
        User::factory()->create(['name' => 'Bob Johnson']);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users?search=John');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating users
    // -------------------------------------------------------------------------

    public function test_store_creates_user_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'student',
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single user
    // -------------------------------------------------------------------------

    public function test_show_returns_user_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/users/' . $this->admin->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->admin->id,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating users
    // -------------------------------------------------------------------------

    public function test_update_modifies_user_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'faculty',
        ];

        $response = $this->putJson('/api/users/' . $this->student->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson('/api/users/' . $this->admin->id, $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting users
    // -------------------------------------------------------------------------

    public function test_deletes_user_for_admin(): void
    {
        $userToDelete = User::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/users/' . $userToDelete->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_delete_prevents_deleting_own_account(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/users/' . $this->admin->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // ME Tests - Current user profile
    // -------------------------------------------------------------------------

    public function test_me_returns_current_user_profile(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/users/me/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->student->id,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // UPDATE PROFILE Tests
    // -------------------------------------------------------------------------

    public function test_update_profile_modifies_current_user(): void
    {
        Sanctum::actingAs($this->student);

        $updateData = [
            'name' => 'New Profile Name',
            'phone' => '9876543210',
        ];

        $response = $this->putJson('/api/users/me/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // CHANGE PASSWORD Tests
    // -------------------------------------------------------------------------

    public function test_change_password_with_valid_credentials(): void
    {
        Sanctum::actingAs($this->student);

        $passwordData = [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->postJson('/api/users/me/change-password', $passwordData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);
    }
}
