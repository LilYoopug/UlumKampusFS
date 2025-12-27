<?php

namespace Tests\Feature\Api;

use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FacultyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing faculties
    // -------------------------------------------------------------------------

    public function test_index_returns_faculties_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Faculty::factory()->count(3)->create(['is_active' => true]);
        Faculty::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/faculties');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_index_only_returns_active_faculties(): void
    {
        Faculty::factory()->create(['name' => 'Active Faculty', 'is_active' => true]);
        Faculty::factory()->create(['name' => 'Inactive Faculty', 'is_active' => false]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/faculties');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $names = array_column($data, 'name');
        $this->assertContains('Active Faculty', $names);
        $this->assertNotContains('Inactive Faculty', $names);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/faculties');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating faculties
    // -------------------------------------------------------------------------

    public function test_store_creates_faculty_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $facultyData = [
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'School of Engineering',
            'dean_name' => 'Dr. Smith',
            'email' => 'engineering@university.edu',
            'phone' => '555-1234',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/faculties', $facultyData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Faculty created successfully',
                'data' => [
                    'name' => 'Engineering',
                    'code' => 'ENG',
                ],
            ]);

        $this->assertDatabaseHas('faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);
    }

    public function test_store_creates_faculty_for_faculty_user(): void
    {
        Sanctum::actingAs($this->faculty);

        $facultyData = [
            'name' => 'Science',
            'code' => 'SCI',
            'description' => 'School of Science',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/faculties', $facultyData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('faculties', [
            'name' => 'Science',
            'code' => 'SCI',
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $facultyData = [
            'name' => 'Arts',
            'code' => 'ART',
        ];

        $response = $this->postJson('/api/faculties', $facultyData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/faculties', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code']);
    }

    public function test_store_prevents_duplicate_codes(): void
    {
        Faculty::factory()->create(['code' => 'UNIQUE']);

        Sanctum::actingAs($this->admin);

        $facultyData = [
            'name' => 'Another Faculty',
            'code' => 'UNIQUE',
        ];

        $response = $this->postJson('/api/faculties', $facultyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single faculty
    // -------------------------------------------------------------------------

    public function test_show_returns_faculty_for_authenticated_user(): void
    {
        $faculty = Faculty::factory()->create([
            'name' => 'Medicine',
            'code' => 'MED',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/faculties/' . $faculty->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $faculty->id,
                    'name' => 'Medicine',
                    'code' => 'MED',
                ],
            ]);
    }

    public function test_show_returns_404_for_nonexistent_faculty(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/faculties/99999');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating faculties
    // -------------------------------------------------------------------------

    public function test_update_modifies_faculty_for_admin(): void
    {
        $faculty = Faculty::factory()->create([
            'name' => 'Original Name',
            'code' => 'ORG',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'name' => 'Updated Name',
            'dean_name' => 'Dr. Johnson',
        ];

        $response = $this->putJson('/api/faculties/' . $faculty->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Faculty updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'dean_name' => 'Dr. Johnson',
                ],
            ]);

        $faculty->refresh();
        $this->assertEquals('Updated Name', $faculty->name);
        $this->assertEquals('Dr. Johnson', $faculty->dean_name);
    }

    public function test_update_modifies_faculty_for_faculty_user(): void
    {
        $faculty = Faculty::factory()->create(['name' => 'Original']);

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'name' => 'Updated',
        ];

        $response = $this->putJson('/api/faculties/' . $faculty->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $faculty = Faculty::factory()->create();

        Sanctum::actingAs($this->student);

        $updateData = [
            'name' => 'Hacked Name',
        ];

        $response = $this->putJson('/api/faculties/' . $faculty->id, $updateData);

        $response->assertStatus(403);
    }

    public function test_update_prevents_code_conflict(): void
    {
        Faculty::factory()->create(['code' => 'EXISTING']);
        $faculty = Faculty::factory()->create(['code' => 'MINE']);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'code' => 'EXISTING',
        ];

        $response = $this->putJson('/api/faculties/' . $faculty->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting faculties
    // -------------------------------------------------------------------------

    public function test_deletes_faculty_for_admin(): void
    {
        $faculty = Faculty::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/faculties/' . $faculty->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('faculties', ['id' => $faculty->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $faculty = Faculty::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/faculties/' . $faculty->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MAJORS Tests - Getting majors for a faculty
    // -------------------------------------------------------------------------

    public function test_majors_returns_facultys_majors(): void
    {
        $faculty = Faculty::factory()->create();
        Major::factory()->create(['faculty_id' => $faculty->id, 'name' => 'Computer Science', 'is_active' => true]);
        Major::factory()->create(['faculty_id' => $faculty->id, 'name' => 'Software Engineering', 'is_active' => true]);
        Major::factory()->create(['faculty_id' => $faculty->id, 'name' => 'Inactive Major', 'is_active' => false]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/faculties/' . $faculty->id . '/majors');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $names = array_column($data, 'name');
        $this->assertContains('Computer Science', $names);
        $this->assertContains('Software Engineering', $names);
        $this->assertNotContains('Inactive Major', $names);
    }

    // -------------------------------------------------------------------------
    // COURSES Tests - Getting courses for a faculty
    // -------------------------------------------------------------------------

    public function test_courses_returns_facultys_courses(): void
    {
        $faculty = Faculty::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/faculties/' . $faculty->id . '/courses');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // MY COURSES Tests - Getting courses for faculty user
    // -------------------------------------------------------------------------

    public function test_my_courses_returns_instructors_courses(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/faculty/my-courses');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // STATS Tests - Getting faculty statistics
    // -------------------------------------------------------------------------

    public function test_stats_returns_faculty_statistics(): void
    {
        $faculty = Faculty::factory()->create();
        $facultyUser = User::factory()->create([
            'role' => 'faculty',
            'faculty_id' => $faculty->id,
        ]);

        Sanctum::actingAs($facultyUser);

        $response = $this->getJson('/api/faculty/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_students' => 0,
                    'total_courses' => 0,
                    'active_courses' => 0,
                    'total_majors' => 0,
                ],
            ]);
    }
}