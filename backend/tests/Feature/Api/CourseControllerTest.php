<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected User $instructor;
    protected Faculty $facultyModel;
    protected Major $majorModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->faculty = User::factory()->create(['role' => 'faculty']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->instructor = User::factory()->create(['role' => 'faculty']);

        // Create faculty and major for testing
        $this->facultyModel = Faculty::factory()->create();
        $this->majorModel = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing courses
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_courses_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        Course::factory()->count(5)->create();

        $response = $this->getJson('/api/courses');

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

    public function test_index_filters_courses_by_faculty(): void
    {
        $otherFaculty = Faculty::factory()->create();
        $otherMajor = Major::factory()->create(['faculty_id' => $otherFaculty->id]);

        Course::factory()->create(['faculty_id' => $this->facultyModel->id]);
        Course::factory()->create(['faculty_id' => $otherFaculty->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses?faculty_id={$this->facultyModel->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertEquals($this->facultyModel->id, $course['faculty_id']);
        }
    }

    public function test_index_filters_courses_by_major(): void
    {
        $otherMajor = Major::factory()->create(['faculty_id' => $this->facultyModel->id]);

        Course::factory()->create(['major_id' => $this->majorModel->id]);
        Course::factory()->create(['major_id' => $otherMajor->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses?major_id={$this->majorModel->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertEquals($this->majorModel->id, $course['major_id']);
        }
    }

    public function test_index_filters_courses_by_instructor(): void
    {
        $otherInstructor = User::factory()->create(['role' => 'faculty']);

        Course::factory()->create(['instructor_id' => $this->instructor->id]);
        Course::factory()->create(['instructor_id' => $otherInstructor->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses?instructor_id={$this->instructor->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertEquals($this->instructor->id, $course['instructor_id']);
        }
    }

    public function test_index_filters_courses_by_semester(): void
    {
        Course::factory()->create(['semester' => 'Fall']);
        Course::factory()->create(['semester' => 'Spring']);
        Course::factory()->create(['semester' => 'Summer']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?semester=Fall');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertEquals('Fall', $course['semester']);
        }
    }

    public function test_index_filters_courses_by_year(): void
    {
        Course::factory()->create(['year' => 2024]);
        Course::factory()->create(['year' => 2025]);
        Course::factory()->create(['year' => 2024]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?year=2024');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertEquals(2024, $course['year']);
        }
    }

    public function test_index_filters_courses_by_active_status(): void
    {
        Course::factory()->create(['is_active' => true]);
        Course::factory()->create(['is_active' => false]);
        Course::factory()->create(['is_active' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?is_active=true');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $course) {
            $this->assertTrue($course['is_active']);
        }
    }

    public function test_index_searches_courses_by_code(): void
    {
        Course::factory()->create(['code' => 'CS101']);
        Course::factory()->create(['code' => 'MATH201']);
        Course::factory()->create(['code' => 'CS202']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?search=CS');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_searches_courses_by_name(): void
    {
        Course::factory()->create(['name' => 'Introduction to Computer Science']);
        Course::factory()->create(['name' => 'Calculus I']);
        Course::factory()->create(['name' => 'Advanced Computer Science']);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?search=Computer');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(401);
    }

    public function test_index_respects_per_page_parameter(): void
    {
        Course::factory()->count(20)->create();

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'meta' => [
                        'per_page' => 5,
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating courses
    // -------------------------------------------------------------------------

    public function test_store_creates_course_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $courseData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS101',
            'name' => 'Introduction to Computer Science',
            'description' => 'A comprehensive introduction to programming.',
            'credit_hours' => 3,
            'capacity' => 50,
            'semester' => 'Fall',
            'year' => 2024,
            'schedule' => 'Mon/Wed 10:00-11:30',
            'room' => 'Science Building 101',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Course created successfully',
            ]);

        $this->assertDatabaseHas('courses', [
            'code' => 'CS101',
            'name' => 'Introduction to Computer Science',
        ]);
    }

    public function test_store_creates_course_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $courseData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS102',
            'name' => 'Data Structures',
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Course created successfully',
            ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $courseData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS101',
            'name' => 'Introduction to Computer Science',
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/courses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['faculty_id', 'major_id', 'instructor_id', 'code', 'name']);
    }

    public function test_store_validates_unique_code(): void
    {
        Course::factory()->create(['code' => 'CS101']);

        Sanctum::actingAs($this->admin);

        $courseData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS101',
            'name' => 'Another Course',
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_store_sets_default_values(): void
    {
        Sanctum::actingAs($this->admin);

        $courseData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS103',
            'name' => 'Algorithms',
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'code' => 'CS103',
            'is_active' => true,
            'current_enrollment' => 0,
        ]);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single course
    // -------------------------------------------------------------------------

    public function test_show_returns_course_for_authenticated_user(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $course->id,
                ],
            ]);
    }

    public function test_show_returns_course_with_modules(): void
    {
        $course = Course::factory()->create();
        $module = \App\Models\CourseModule::factory()->create(['course_id' => $course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_show_returns_404_for_nonexistent_course(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $course = Course::factory()->create();

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating courses
    // -------------------------------------------------------------------------

    public function test_update_modifies_course_for_admin(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->admin);

        $updateData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => $course->code,
            'name' => 'Updated Course Name',
            'description' => 'Updated description',
            'credit_hours' => 4,
            'capacity' => 60,
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course updated successfully',
            ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Updated Course Name',
        ]);
    }

    public function test_update_modifies_course_for_faculty(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->faculty);

        $updateData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => $course->code,
            'name' => 'Updated by Faculty',
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course updated successfully',
            ]);
    }

    public function test_update_fails_for_student(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->student);

        $updateData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => $course->code,
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_update_allows_same_code_for_same_course(): void
    {
        $course = Course::factory()->create(['code' => 'CS101']);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS101', // Same code
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/courses/{$course->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_update_validates_unique_code_across_different_courses(): void
    {
        $course1 = Course::factory()->create(['code' => 'CS101']);
        $course2 = Course::factory()->create(['code' => 'CS102']);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'faculty_id' => $this->facultyModel->id,
            'major_id' => $this->majorModel->id,
            'instructor_id' => $this->instructor->id,
            'code' => 'CS101', // Code from course1
            'name' => 'Updated Name',
        ];

        $response = $this->putJson("/api/courses/{$course2->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting courses
    // -------------------------------------------------------------------------

    public function test_delete_removes_course_for_admin(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_delete_removes_course_for_faculty(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->faculty);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(403);
    }

    public function test_delete_prevents_deleting_course_with_active_enrollments(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete course with active enrollments. Please drop all students first.',
            ]);

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_delete_allows_deleting_course_with_dropped_enrollments_only(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    }

    // -------------------------------------------------------------------------
    // MODULES Tests
    // -------------------------------------------------------------------------

    public function test_modules_returns_course_modules(): void
    {
        $course = Course::factory()->create();
        \App\Models\CourseModule::factory()->count(3)->create(['course_id' => $course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/modules");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course modules retrieved successfully',
            ]);
    }

    public function test_modules_returns_404_for_nonexistent_course(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses/99999/modules');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // ENROLLMENTS Tests
    // -------------------------------------------------------------------------

    public function test_enrollments_returns_course_enrollments(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->count(3)->create(['course_id' => $course->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/courses/{$course->id}/enrollments");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course enrollments retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // STUDENTS Tests
    // -------------------------------------------------------------------------

    public function test_students_returns_enrolled_students(): void
    {
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/students");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Enrolled students retrieved successfully',
            ]);
    }

    public function test_students_excludes_dropped_students(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'status' => 'enrolled',
        ]);
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/students");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // ASSIGNMENTS Tests
    // -------------------------------------------------------------------------

    public function test_assignments_returns_course_assignments(): void
    {
        $course = Course::factory()->create();
        \App\Models\Assignment::factory()->count(2)->create(['course_id' => $course->id]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/assignments");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course assignments retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // ANNOUNCEMENTS Tests
    // -------------------------------------------------------------------------

    public function test_announcements_returns_published_announcements(): void
    {
        $course = Course::factory()->create();
        \App\Models\Announcement::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);
        \App\Models\Announcement::factory()->create([
            'course_id' => $course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/announcements");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // LIBRARY RESOURCES Tests
    // -------------------------------------------------------------------------

    public function test_library_resources_returns_published_resources(): void
    {
        $course = Course::factory()->create();
        \App\Models\LibraryResource::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);
        \App\Models\LibraryResource::factory()->create([
            'course_id' => $course->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/library-resources");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // DISCUSSION THREADS Tests
    // -------------------------------------------------------------------------

    public function test_discussion_threads_returns_open_threads(): void
    {
        $course = Course::factory()->create();
        \App\Models\DiscussionThread::factory()->create([
            'course_id' => $course->id,
            'status' => 'open',
        ]);
        \App\Models\DiscussionThread::factory()->create([
            'course_id' => $course->id,
            'status' => 'closed',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson("/api/courses/{$course->id}/discussion-threads");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
    }

    // -------------------------------------------------------------------------
    // GRADES Tests
    // -------------------------------------------------------------------------

    public function test_grades_returns_course_grades(): void
    {
        $course = Course::factory()->create();
        \App\Models\Grade::factory()->count(2)->create(['course_id' => $course->id]);

        Sanctum::actingAs($this->faculty);

        $response = $this->getJson("/api/courses/{$course->id}/grades");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course grades retrieved successfully',
            ]);
    }

    // -------------------------------------------------------------------------
    // ENROLL Tests - Student enrollment
    // -------------------------------------------------------------------------

    public function test_enroll_allows_student_to_enroll_in_active_course(): void
    {
        $course = Course::factory()->create(['is_active' => true, 'capacity' => 50]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully enrolled in course',
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        $course->refresh();
        $this->assertEquals(1, $course->current_enrollment);
    }

    public function test_enroll_fails_for_inactive_course(): void
    {
        $course = Course::factory()->create(['is_active' => false]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot enroll in an inactive course',
            ]);
    }

    public function test_enroll_fails_for_full_course(): void
    {
        $course = Course::factory()->create([
            'capacity' => 30,
            'current_enrollment' => 30,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Course is at full capacity',
            ]);
    }

    public function test_enroll_fails_for_already_enrolled_student(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Already enrolled in this course',
            ]);
    }

    public function test_enroll_allows_re_enrollment_for_dropped_student(): void
    {
        $course = Course::factory()->create(['capacity' => 50, 'current_enrollment' => 0]);
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully re-enrolled in course',
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);
    }

    public function test_enroll_fails_for_non_student_role(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/courses/{$course->id}/enroll");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DROP Tests - Student drop
    // -------------------------------------------------------------------------

    public function test_drop_allows_student_to_drop_course(): void
    {
        $course = Course::factory()->create(['capacity' => 50, 'current_enrollment' => 1]);
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'enrolled',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/drop");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully dropped from course',
            ]);

        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        $course->refresh();
        $this->assertEquals(0, $course->current_enrollment);
    }

    public function test_drop_fails_for_already_dropped_student(): void
    {
        $course = Course::factory()->create();
        CourseEnrollment::factory()->create([
            'course_id' => $course->id,
            'student_id' => $this->student->id,
            'status' => 'dropped',
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/drop");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Already dropped from this course',
            ]);
    }

    public function test_drop_fails_for_non_student_role(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/courses/{$course->id}/drop");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // MY COURSES Tests
    // -------------------------------------------------------------------------

    public function test_my_courses_returns_instructor_courses(): void
    {
        Course::factory()->create(['instructor_id' => $this->instructor->id]);
        Course::factory()->create(['instructor_id' => $this->instructor->id]);
        Course::factory()->create(['instructor_id' => $this->faculty->id]);

        Sanctum::actingAs($this->instructor);

        $response = $this->getJson('/api/courses/my-courses');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'My courses retrieved successfully',
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_my_courses_fails_for_non_faculty_role(): void
    {
        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/courses/my-courses');

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // TOGGLE STATUS Tests
    // -------------------------------------------------------------------------

    public function test_toggle_status_changes_active_status(): void
    {
        $course = Course::factory()->create(['is_active' => true]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/courses/{$course->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Course status updated successfully',
            ]);

        $course->refresh();
        $this->assertFalse($course->is_active);
    }

    public function test_toggle_status_toggles_back_to_active(): void
    {
        $course = Course::factory()->create(['is_active' => false]);

        Sanctum::actingAs($this->faculty);

        $response = $this->postJson("/api/courses/{$course->id}/toggle-status");

        $response->assertStatus(200);

        $course->refresh();
        $this->assertTrue($course->is_active);
    }

    public function test_toggle_status_fails_for_student(): void
    {
        $course = Course::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->postJson("/api/courses/{$course->id}/toggle-status");

        $response->assertStatus(403);
    }
}