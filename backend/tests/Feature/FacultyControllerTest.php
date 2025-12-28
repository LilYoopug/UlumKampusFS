<?php

namespace Tests\Feature;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $faculty;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    // ============================================
    // Index - List Faculties
    // ============================================

    public function test_authenticated_user_can_list_all_faculties(): void
    {
        Faculty::factory()->count(5)->create();

        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'description',
                        'dean_name',
                        'email',
                        'phone',
                        'is_active',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    public function test_unauthenticated_user_cannot_list_faculties(): void
    {
        Faculty::factory()->count(3)->create();

        $response = $this->getJson('/api/faculties');

        $response->assertStatus(401);
    }

    public function test_index_only_returns_active_faculties(): void
    {
        Faculty::factory()->count(3)->create(['is_active' => true]);
        Faculty::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(3, $data);

        foreach ($data as $faculty) {
            $this->assertTrue($faculty['is_active']);
        }
    }

    // ============================================
    // Show - View Single Faculty
    // ============================================

    public function test_authenticated_user_can_view_single_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->actingAs($this->student)
            ->getJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $faculty->id,
                    'name' => $faculty->name,
                    'code' => $faculty->code,
                ],
            ]);
    }

    public function test_show_includes_majors_relationship(): void
    {
        $faculty = Faculty::factory()->create();
        \App\Models\Major::factory()->createMany([
            ['faculty_id' => $faculty->id, 'name' => 'Computer Science', 'code' => 'CS'],
            ['faculty_id' => $faculty->id, 'name' => 'Information Technology', 'code' => 'IT'],
        ]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('majors', $data);
        $this->assertCount(2, $data['majors']);
    }

    public function test_viewing_nonexistent_faculty_returns_404(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties/999999');

        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_view_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->getJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(401);
    }

    // ============================================
    // Store - Create Faculty
    // ============================================

    public function test_admin_can_create_faculty(): void
    {
        $facultyData = [
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Faculty of Engineering',
            'dean_name' => 'Dr. John Doe',
            'email' => 'engineering@example.com',
            'phone' => '+1234567890',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/faculties', $facultyData);

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

    public function test_faculty_can_create_faculty(): void
    {
        $facultyData = [
            'name' => 'Science',
            'code' => 'SCI',
            'description' => 'Faculty of Science',
        ];

        $response = $this->actingAs($this->faculty)
            ->postJson('/api/faculties', $facultyData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('faculties', [
            'name' => 'Science',
            'code' => 'SCI',
        ]);
    }

    public function test_student_cannot_create_faculty(): void
    {
        $facultyData = [
            'name' => 'Arts',
            'code' => 'ART',
        ];

        $response = $this->actingAs($this->student)
            ->postJson('/api/faculties', $facultyData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('faculties', [
            'name' => 'Arts',
        ]);
    }

    public function test_creating_faculty_without_name_fails(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/faculties', [
                'code' => 'ENG',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_creating_faculty_without_code_fails(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/faculties', [
                'name' => 'Engineering',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_creating_faculty_with_duplicate_code_fails(): void
    {
        Faculty::factory()->create(['code' => 'ENG']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/faculties', [
                'name' => 'Engineering 2',
                'code' => 'ENG',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // ============================================
    // Update - Update Faculty
    // ============================================

    public function test_admin_can_update_faculty(): void
    {
        $faculty = Faculty::factory()->create([
            'name' => 'Original Name',
            'code' => 'ORG',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'code' => 'UPD',
            'description' => 'Updated description',
            'dean_name' => 'Dr. Jane Doe',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/faculties/{$faculty->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Faculty updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'code' => 'UPD',
                ],
            ]);

        $this->assertDatabaseHas('faculties', [
            'id' => $faculty->id,
            'name' => 'Updated Name',
            'code' => 'UPD',
        ]);
    }

    public function test_faculty_can_update_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $updateData = [
            'name' => 'Faculty Updated',
            'code' => 'FAC',
        ];

        $response = $this->actingAs($this->faculty)
            ->putJson("/api/faculties/{$faculty->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_student_cannot_update_faculty(): void
    {
        $faculty = Faculty::factory()->create(['name' => 'Original']);

        $updateData = [
            'name' => 'Student Attempt',
        ];

        $response = $this->actingAs($this->student)
            ->putJson("/api/faculties/{$faculty->id}", $updateData);

        $response->assertStatus(403);

        $this->assertDatabaseHas('faculties', [
            'id' => $faculty->id,
            'name' => 'Original',
        ]);
    }

    public function test_updating_faculty_with_own_code_succeeds(): void
    {
        $faculty = Faculty::factory()->create(['code' => 'ENG']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/faculties/{$faculty->id}", [
                'name' => 'Updated Name',
                'code' => 'ENG',
            ]);

        $response->assertStatus(200);
    }

    public function test_updating_faculty_with_duplicate_code_fails(): void
    {
        Faculty::factory()->create(['code' => 'ENG']);
        $faculty = Faculty::factory()->create(['code' => 'SCI']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/faculties/{$faculty->id}", [
                'name' => 'Updated',
                'code' => 'ENG',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // ============================================
    // Destroy - Delete Faculty
    // ============================================

    public function test_admin_can_delete_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(204);

        // Since Faculty uses SoftDeletes, we check if it's soft-deleted
        $this->assertSoftDeleted('faculties', [
            'id' => $faculty->id,
        ]);
    }

    public function test_faculty_can_delete_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->actingAs($this->faculty)
            ->deleteJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(204);

        // Since Faculty uses SoftDeletes, we check if it's soft-deleted
        $this->assertSoftDeleted('faculties', [
            'id' => $faculty->id,
        ]);
    }

    public function test_student_cannot_delete_faculty(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->actingAs($this->student)
            ->deleteJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('faculties', [
            'id' => $faculty->id,
        ]);
    }

    public function test_deleting_nonexistent_faculty_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson('/api/faculties/999999');

        $response->assertStatus(404);
    }

    // ============================================
    // Majors - Get Faculty Majors
    // ============================================

    public function test_can_get_majors_for_faculty(): void
    {
        $faculty = Faculty::factory()->create();
        \App\Models\Major::factory()->createMany([
            ['faculty_id' => $faculty->id, 'name' => 'Computer Science', 'code' => 'CS', 'is_active' => true],
            ['faculty_id' => $faculty->id, 'name' => 'Information Technology', 'code' => 'IT', 'is_active' => true],
            ['faculty_id' => $faculty->id, 'name' => 'Software Engineering', 'code' => 'SE', 'is_active' => false],
        ]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/faculties/{$faculty->id}/majors");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Only active majors

        foreach ($data as $major) {
            $this->assertTrue($major['is_active']);
        }
    }

    public function test_getting_majors_for_nonexistent_faculty_returns_404(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties/999999/majors');

        $response->assertStatus(404);
    }

    // ============================================
    // Courses - Get Faculty Courses
    // ============================================

    public function test_can_get_courses_for_faculty(): void
    {
        $faculty = Faculty::factory()->create();
        $faculty->courses()->createMany([
            ['name' => 'Introduction to Programming', 'code' => 'CS101', 'is_active' => true],
            ['name' => 'Data Structures', 'code' => 'CS201', 'is_active' => true],
            ['name' => 'Advanced Algorithms', 'code' => 'CS301', 'is_active' => false],
        ]);

        $response = $this->actingAs($this->student)
            ->getJson("/api/faculties/{$faculty->id}/courses");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Only active courses

        foreach ($data as $course) {
            $this->assertTrue($course['is_active']);
        }
    }

    public function test_getting_courses_for_nonexistent_faculty_returns_404(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties/999999/courses');

        $response->assertStatus(404);
    }

    // ============================================
    // Response Structure Tests
    // ============================================

    public function test_faculty_response_has_correct_structure(): void
    {
        $faculty = Faculty::factory()->create();

        $response = $this->actingAs($this->student)
            ->getJson("/api/faculties/{$faculty->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'code',
                    'description',
                    'dean_name',
                    'email',
                    'phone',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_list_response_has_correct_structure(): void
    {
        Faculty::factory()->count(3)->create();

        $response = $this->actingAs($this->student)
            ->getJson('/api/faculties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'description',
                        'dean_name',
                        'email',
                        'phone',
                        'is_active',
                    ],
                ],
            ]);
    }
}