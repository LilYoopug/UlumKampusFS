<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    // -------------------------------------------------------------------------
    // STATS Tests - Admin dashboard statistics
    // -------------------------------------------------------------------------

    public function test_stats_returns_admin_statistics_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        // Create some test data
        User::factory()->count(5)->create(['role' => 'student']);
        User::factory()->count(2)->create(['role' => 'faculty']);
        Course::factory()->count(3)->create(['is_active' => true]);
        Course::factory()->create(['is_active' => false]);
        CourseEnrollment::factory()->count(4)->create(['status' => 'enrolled']);
        CourseEnrollment::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_users' => 10, // admin + faculty + 5 students + 2 faculty + user from setUp
                    'total_students' => 6,
                    'total_faculty' => 3,
                    'total_admins' => 1,
                    'total_courses' => 4,
                    'active_courses' => 3,
                    'total_enrollments' => 5,
                    'active_enrollments' => 4,
                ],
            ]);
    }

    public function test_stats_returns_empty_stats_for_new_installation(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_users' => 3, // admin + faculty + student
                    'total_students' => 1,
                    'total_faculty' => 1,
                    'total_admins' => 1,
                    'total_courses' => 0,
                    'active_courses' => 0,
                    'total_enrollments' => 0,
                    'active_enrollments' => 0,
                ],
            ]);
    }

    public function test_stats_requires_admin_role(): void
    {
        Sanctum::actingAs($this->faculty);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(403);
    }

    public function test_stats_requires_authentication(): void
    {
        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // USERS Tests - Getting all users
    // -------------------------------------------------------------------------

    public function test_users_returns_all_users_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(5)->create(['role' => 'student']);
        User::factory()->count(2)->create(['role' => 'faculty']);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(10, count($data));
    }

    public function test_users_returns_users_with_relationships(): void
    {
        Sanctum::actingAs($this->admin);

        $userWithFaculty = User::factory()->create(['role' => 'student']);
        $userWithMajor = User::factory()->create(['role' => 'student']);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    public function test_users_requires_admin_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_users_requires_authentication(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // DASHBOARD Tests
    // -------------------------------------------------------------------------

    public function test_dashboard_returns_welcome_message_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Welcome Admin!',
            ]);
    }

    public function test_dashboard_requires_admin_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Statistics aggregation tests
    // -------------------------------------------------------------------------

    public function test_stats_correctly_counts_enrolled_students(): void
    {
        Sanctum::actingAs($this->admin);

        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        CourseEnrollment::factory()->create([
            'course_id' => $course1->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course1->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course2->id,
            'status' => 'pending',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course2->id,
            'status' => 'dropped',
        ]);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(4, $data['total_enrollments']);
        $this->assertEquals(2, $data['active_enrollments']);
    }

    public function test_stats_correctly_counts_active_courses(): void
    {
        Sanctum::actingAs($this->admin);

        Course::factory()->count(5)->create(['is_active' => true]);
        Course::factory()->count(3)->create(['is_active' => false]);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(8, $data['total_courses']);
        $this->assertEquals(5, $data['active_courses']);
    }

    public function test_stats_correctly_counts_users_by_role(): void
    {
        Sanctum::actingAs($this->admin);

        User::factory()->count(10)->create(['role' => 'student']);
        User::factory()->count(5)->create(['role' => 'faculty']);
        User::factory()->count(2)->create(['role' => 'admin']);

        $response = $this->getJson('/api/admin/stats');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(12, $data['total_students']); // 1 from setUp + 10
        $this->assertEquals(6, $data['total_faculty']); // 1 from setUp + 5
        $this->assertEquals(3, $data['total_admins']); // 1 from setUp + 2
    }
}