<?php

namespace Tests\Feature;

use App\Http\Requests\FacultyRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacultyRequestTest extends TestCase
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
    // Authorization Tests
    // ============================================

    public function test_admin_is_authorized_to_create_faculty(): void
    {
        $request = new FacultyRequest();
        $request->setMethod('POST');
        $request->merge(['name' => 'Engineering', 'code' => 'ENG']);

        $this->actingAs($this->admin);

        $this->assertTrue($request->authorize());
    }

    public function test_faculty_is_authorized_to_create_faculty(): void
    {
        $request = new FacultyRequest();
        $request->setMethod('POST');
        $request->merge(['name' => 'Engineering', 'code' => 'ENG']);

        $this->actingAs($this->faculty);

        $this->assertTrue($request->authorize());
    }

    public function test_student_is_not_authorized_to_create_faculty(): void
    {
        $request = new FacultyRequest();
        $request->setMethod('POST');
        $request->merge(['name' => 'Engineering', 'code' => 'ENG']);

        $this->actingAs($this->student);

        $this->assertFalse($request->authorize());
    }

    public function test_unauthenticated_user_is_not_authorized(): void
    {
        $request = new FacultyRequest();
        $request->setMethod('POST');
        $request->merge(['name' => 'Engineering', 'code' => 'ENG']);

        $this->assertFalse($request->authorize());
    }

    // ============================================
    // Validation Rules Tests
    // ============================================

    public function test_name_is_required(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'code' => 'ENG',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_name_must_be_string(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 12345,
            'code' => 'ENG',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_name_must_not_exceed_255_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => str_repeat('a', 256),
            'code' => 'ENG',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_code_is_required(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_be_string(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 12345,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_not_exceed_50_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => str_repeat('A', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_be_unique(): void
    {
        $this->actingAs($this->admin);

        // Create first faculty
        $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        // Try to create second faculty with same code
        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering 2',
            'code' => 'ENG',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_be_unique_ignoring_self_on_update(): void
    {
        $this->actingAs($this->admin);

        // Create faculty
        $createResponse = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $facultyId = $createResponse->json('data.id');

        // Update with same code should work
        $response = $this->putJson("/api/faculties/{$facultyId}", [
            'name' => 'Engineering Updated',
            'code' => 'ENG',
        ]);

        $response->assertStatus(200);
    }

    public function test_code_must_be_unique_for_different_faculty_on_update(): void
    {
        $this->actingAs($this->admin);

        // Create first faculty
        $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        // Create second faculty
        $createResponse = $this->postJson('/api/faculties', [
            'name' => 'Science',
            'code' => 'SCI',
        ]);

        $facultyId = $createResponse->json('data.id');

        // Try to update second faculty with first faculty's code
        $response = $this->putJson("/api/faculties/{$facultyId}", [
            'name' => 'Science Updated',
            'code' => 'ENG',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_be_uppercase(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'eng',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_code_must_contain_only_letters_and_numbers(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_description_is_optional(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $response->assertStatus(201);
    }

    public function test_description_must_be_string_if_provided(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 12345,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_description_must_not_exceed_2000_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => str_repeat('a', 2001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    public function test_dean_name_is_optional(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $response->assertStatus(201);
    }

    public function test_dean_name_must_be_string_if_provided(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'dean_name' => 12345,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dean_name']);
    }

    public function test_dean_name_must_not_exceed_255_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'dean_name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dean_name']);
    }

    public function test_email_is_optional(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $response->assertStatus(201);
    }

    public function test_email_must_be_valid_email_if_provided(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_must_not_exceed_255_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'email' => str_repeat('a', 240) . '@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_phone_is_optional(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $response->assertStatus(201);
    }

    public function test_phone_must_be_string_if_provided(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'phone' => 12345,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_phone_must_not_exceed_50_characters(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'phone' => str_repeat('1', 51),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_is_active_must_be_boolean_if_provided(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
            'is_active' => 'yes',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_active']);
    }

    public function test_is_active_defaults_to_true(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_active' => true,
                ],
            ]);
    }

    // ============================================
    // Custom Error Messages Tests
    // ============================================

    public function test_custom_error_message_for_name_required(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'code' => 'ENG',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors.name');
        $this->assertNotEmpty($errors);
    }

    public function test_custom_error_message_for_code_format(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', [
            'name' => 'Engineering',
            'code' => 'eng',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors.code');
        $this->assertNotEmpty($errors);
    }

    // ============================================
    // Validation Response Structure Tests
    // ============================================

    public function test_validation_failure_returns_correct_structure(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson('/api/faculties', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [],
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);
    }
}