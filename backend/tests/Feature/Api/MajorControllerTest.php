<?php

namespace Tests\Feature\Api;

use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MajorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);

        // Create faculty for testing
        $this->facultyModel = Faculty::factory()->create();
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing majors
    // -------------------------------------------------------------------------

    public function test_index_returns_majors_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Major::factory()->count(3)->create([
            'faculty_id' => $this->facultyModel->id,
            'is_active' => true,
        ]);
        Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/majors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_index_only_returns_active_majors(): void
    {
        Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Active Major',
            'is_active' => true,
        ]);
        Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Inactive Major',
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $names = array_column($data, 'name');
        $this->assertContains('Active Major', $names);
        $this->assertNotContains('Inactive Major', $names);
    }

    public function test_index_includes_faculty_data(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('faculty', $data[0]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/majors');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating majors
    // -------------------------------------------------------------------------

    public function test_store_creates_major_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Computer Science program',
            'head_of_program' => 'Dr. Johnson',
            'email' => 'cs@university.edu',
            'phone' => '555-5678',
            'duration_years' => 4,
            'credit_hours' => 120,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Major created successfully',
                'data' => [
                    'name' => 'Computer Science',
                    'code' => 'CS',
                ],
            ]);

        $this->assertDatabaseHas('majors', [
            'name' => 'Computer Science',
            'code' => 'CS',
            'faculty_id' => $this->facultyModel->id,
        ]);
    }

    public function test_store_creates_major_for_faculty_user(): void
    {
        Sanctum::actingAs($this->faculty);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('majors', [
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Hacked Major',
            'code' => 'HACK',
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/majors', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['faculty_id', 'name', 'code']);
    }

    public function test_store_validates_faculty_exists(): void
    {
        Sanctum::actingAs($this->admin);

        $majorData = [
            'faculty_id' => 99999,
            'name' => 'Test Major',
            'code' => 'TEST',
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['faculty_id']);
    }

    public function test_store_prevents_duplicate_codes(): void
    {
        Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'code' => 'UNIQUE',
        ]);

        Sanctum::actingAs($this->admin);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Another Major',
            'code' => 'UNIQUE',
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_validates_duration_years_range(): void
    {
        Sanctum::actingAs($this->admin);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Test Major',
            'code' => 'TEST',
            'duration_years' => 15, // Exceeds max of 10
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_years']);
    }

    public function test_store_validates_email_format(): void
    {
        Sanctum::actingAs($this->admin);

        $majorData = [
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Test Major',
            'code' => 'TEST',
            'email' => 'invalid-email',
        ];

        $response = $this->postJson('/api/majors', $majorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single major
    // -------------------------------------------------------------------------

    public function test_show_returns_major_for_authenticated_user(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Physics',
            'code' => 'PHY',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors/' . $major->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $major->id,
                    'name' => 'Physics',
                    'code' => 'PHY',
                ],
            ]);
    }

    public function test_show_includes_faculty_data(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors/' . $major->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('faculty', $data);
        $this->assertEquals($this->facultyModel->id, $data['faculty']['id']);
    }

    public function test_show_returns_404_for_nonexistent_major(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating majors
    // -------------------------------------------------------------------------

    public function test_update_modifies_major_for_admin(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Original Name',
            'code' => 'ORG',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Name',
            'head_of_program' => 'Dr. Williams',
        ];

        $response = $this->putJson('/api/majors/' . $major->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Major updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'head_of_program' => 'Dr. Williams',
                ],
            ]);

        $major->refresh();
        $this->assertEquals('Updated Name', $major->name);
        $this->assertEquals('Dr. Williams', $major->head_of_program);
    }

    public function test_update_modifies_major_for_faculty_user(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'name' => 'Original',
        ]);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'name' => 'Updated',
        ];

        $response = $this->putJson('/api/majors/' . $major->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->student);

        $updateData = [
            'name' => 'Hacked Name',
        ];

        $response = $this->putJson('/api/majors/' . $major->id, $updateData);

        $response->assertStatus(403);
    }

    public function test_update_prevents_code_conflict(): void
    {
        Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'code' => 'EXISTING',
        ]);
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'code' => 'MINE',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'code' => 'EXISTING',
        ];

        $response = $this->putJson('/api/majors/' . $major->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_update_allows_same_code_for_same_major(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
            'code' => 'SAME',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Name',
            'code' => 'SAME', // Same code, same major - should be allowed
        ];

        $response = $this->putJson('/api/majors/' . $major->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting majors
    // -------------------------------------------------------------------------

    public function test_deletes_major_for_admin(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/majors/' . $major->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('majors', ['id' => $major->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/majors/' . $major->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // FACULTY Tests - Getting faculty for a major
    // -------------------------------------------------------------------------

    public function test_faculty_returns_majors_faculty(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors/' . $major->id . '/faculty');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->facultyModel->id,
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // COURSES Tests - Getting courses for a major
    // -------------------------------------------------------------------------

    public function test_courses_returns_majors_courses(): void
    {
        $major = Major::factory()->create([
            'faculty_id' => $this->facultyModel->id,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/majors/' . $major->id . '/courses');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}