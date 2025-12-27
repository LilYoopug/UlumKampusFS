<?php

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\LibraryResource;
use App\Models\Major;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LibraryResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $faculty;
    protected User $student;
    protected Faculty $facultyModel;
    protected Major $majorModel;
    protected Course $courseModel;

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
    }

    // -------------------------------------------------------------------------
    // INDEX Tests - Listing library resources
    // -------------------------------------------------------------------------

    public function test_index_returns_resources_for_authenticated_user(): void
    {
        Sanctum::actingAs($this->student);

        LibraryResource::factory()->count(3)->create(['is_published' => true]);

        $response = $this->getJson('/api/library');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_index_filters_resources_by_type(): void
    {
        LibraryResource::factory()->create(['resource_type' => 'book', 'is_published' => true]);
        LibraryResource::factory()->create(['resource_type' => 'video', 'is_published' => true]);
        LibraryResource::factory()->create(['resource_type' => 'book', 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library?resource_type=book');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $resource) {
            $this->assertEquals('book', $resource['resource_type']);
        }
    }

    public function test_index_filters_resources_by_access_level(): void
    {
        LibraryResource::factory()->create(['access_level' => 'public', 'is_published' => true]);
        LibraryResource::factory()->create(['access_level' => 'faculty', 'is_published' => true]);
        LibraryResource::factory()->create(['access_level' => 'public', 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library?access_level=public');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $resource) {
            $this->assertEquals('public', $resource['access_level']);
        }
    }

    public function test_index_searches_resources(): void
    {
        LibraryResource::factory()->create([
            'title' => 'Introduction to Algorithms',
            'author' => 'Thomas Cormen',
            'is_published' => true,
        ]);
        LibraryResource::factory()->create([
            'title' => 'Clean Code',
            'author' => 'Robert Martin',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library?search=algorithms');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_filters_resources_by_publication_year(): void
    {
        LibraryResource::factory()->create(['publication_year' => 2020, 'is_published' => true]);
        LibraryResource::factory()->create(['publication_year' => 2023, 'is_published' => true]);
        LibraryResource::factory()->create(['publication_year' => 2020, 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library?publication_year=2020');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        foreach ($data as $resource) {
            $this->assertEquals(2020, $resource['publication_year']);
        }
    }

    public function test_index_filters_resources_by_tag(): void
    {
        LibraryResource::factory()->create(['tags' => 'programming,algorithms', 'is_published' => true]);
        LibraryResource::factory()->create(['tags' => 'design,ux', 'is_published' => true]);
        LibraryResource::factory()->create(['tags' => 'programming,web', 'is_published' => true]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library?tag=programming');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/library');

        $response->assertStatus(401);
    }

    public function test_index_hides_unpublished_from_students(): void
    {
        LibraryResource::factory()->create(['title' => 'Published Resource', 'is_published' => true]);
        LibraryResource::factory()->create(['title' => 'Draft Resource', 'is_published' => false]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $titles = array_column($data, 'title');
        $this->assertContains('Published Resource', $titles);
        $this->assertNotContains('Draft Resource', $titles);
    }

    // -------------------------------------------------------------------------
    // STORE Tests - Creating library resources
    // -------------------------------------------------------------------------

    public function test_store_creates_resource_for_admin(): void
    {
        Sanctum::actingAs($this->admin);

        $resourceData = [
            'title' => 'Advanced Mathematics',
            'description' => 'Comprehensive guide to advanced mathematical concepts',
            'resource_type' => 'book',
            'access_level' => 'public',
            'author' => 'John Smith',
            'publisher' => 'Academic Press',
            'isbn' => '978-0123456789',
            'publication_year' => 2023,
            'tags' => 'mathematics,calculus,algebra',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/library', $resourceData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Library resource created successfully',
            ]);

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Advanced Mathematics',
            'resource_type' => 'book',
            'access_level' => 'public',
        ]);
    }

    public function test_store_creates_resource_with_file_url(): void
    {
        Sanctum::actingAs($this->admin);

        $resourceData = [
            'title' => 'Course Syllabus',
            'description' => 'Spring 2024 syllabus',
            'resource_type' => 'document',
            'file_url' => 'https://example.com/files/syllabus.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024000,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/library', $resourceData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Course Syllabus',
            'file_url' => 'https://example.com/files/syllabus.pdf',
        ]);
    }

    public function test_store_creates_resource_with_external_link(): void
    {
        Sanctum::actingAs($this->admin);

        $resourceData = [
            'title' => 'Online Tutorial',
            'description' => 'Link to external tutorial',
            'resource_type' => 'link',
            'external_link' => 'https://www.youtube.com/watch?v=example',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/library', $resourceData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Online Tutorial',
            'external_link' => 'https://www.youtube.com/watch?v=example',
        ]);
    }

    public function test_store_creates_resource_for_faculty(): void
    {
        Sanctum::actingAs($this->faculty);

        $resourceData = [
            'title' => 'Faculty Notes',
            'description' => 'Course materials',
            'resource_type' => 'document',
            'course_id' => $this->courseModel->id,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/library', $resourceData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Faculty Notes',
        ]);
    }

    public function test_store_fails_for_student(): void
    {
        Sanctum::actingAs($this->student);

        $resourceData = [
            'title' => 'Student Resource',
            'description' => 'This should not work.',
        ];

        $response = $this->postJson('/api/library', $resourceData);

        $response->assertStatus(403);
    }

    public function test_store_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/library', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    // -------------------------------------------------------------------------
    // SHOW Tests - Retrieving a single resource
    // -------------------------------------------------------------------------

    public function test_show_returns_resource_for_authenticated_user(): void
    {
        $resource = LibraryResource::factory()->create([
            'title' => 'Test Resource',
            'description' => 'Test description',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->getJson('/api/library/' . $resource->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $resource->id,
                    'title' => 'Test Resource',
                ],
            ]);
    }

    public function test_show_increments_view_count(): void
    {
        $resource = LibraryResource::factory()->create([
            'is_published' => true,
            'view_count' => 5,
        ]);

        Sanctum::actingAs($this->student);

        $this->getJson('/api/library/' . $resource->id);

        $resource->refresh();
        $this->assertEquals(6, $resource->view_count);
    }

    // -------------------------------------------------------------------------
    // UPDATE Tests - Updating resources
    // -------------------------------------------------------------------------

    public function test_update_modifies_resource_for_admin(): void
    {
        $resource = LibraryResource::factory()->create([
            'title' => 'Original Title',
            'resource_type' => 'document',
        ]);

        Sanctum::actingAs($this->admin);

        $updateData = [
            'title' => 'Updated Title',
            'resource_type' => 'video',
        ];

        $response = $this->putJson('/api/library/' . $resource->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Library resource updated successfully',
            ]);

        $resource->refresh();
        $this->assertEquals('Updated Title', $resource->title);
        $this->assertEquals('video', $resource->resource_type);
    }

    public function test_update_fails_for_student(): void
    {
        $resource = LibraryResource::factory()->create();

        Sanctum::actingAs($this->student);

        $updateData = [
            'title' => 'Hacked Title',
        ];

        $response = $this->putJson('/api/library/' . $resource->id, $updateData);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DESTROY Tests - Deleting resources
    // -------------------------------------------------------------------------

    public function test_deletes_resource_for_admin(): void
    {
        $resource = LibraryResource::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/library/' . $resource->id);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('library_resources', ['id' => $resource->id]);
    }

    public function test_delete_fails_for_student(): void
    {
        $resource = LibraryResource::factory()->create();

        Sanctum::actingAs($this->student);

        $response = $this->deleteJson('/api/library/' . $resource->id);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DOWNLOAD Tests
    // -------------------------------------------------------------------------

    public function test_download_returns_file_link(): void
    {
        $resource = LibraryResource::factory()->create([
            'title' => 'PDF Document',
            'file_url' => 'https://example.com/files/document.pdf',
            'file_type' => 'application/pdf',
            'is_published' => true,
            'download_count' => 10,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/library/' . $resource->id . '/download');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'url' => 'https://example.com/files/document.pdf',
                    'file_name' => 'PDF Document',
                    'file_type' => 'application/pdf',
                ],
            ]);

        $resource->refresh();
        $this->assertEquals(11, $resource->download_count);
    }

    public function test_download_returns_external_link(): void
    {
        $resource = LibraryResource::factory()->create([
            'title' => 'External Tutorial',
            'external_link' => 'https://www.youtube.com/watch?v=example',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/library/' . $resource->id . '/download');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'url' => 'https://www.youtube.com/watch?v=example',
                ],
            ]);
    }

    public function test_download_fails_for_resource_without_file_or_link(): void
    {
        $resource = LibraryResource::factory()->create([
            'title' => 'No Download',
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->student);

        $response = $this->postJson('/api/library/' . $resource->id . '/download');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'No downloadable resource available',
            ]);
    }

    // -------------------------------------------------------------------------
    // PUBLISH Tests
    // -------------------------------------------------------------------------

    public function test_publish_resource_for_admin(): void
    {
        $resource = LibraryResource::factory()->create([
            'is_published' => false,
            'published_at' => null,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/library/' . $resource->id . '/publish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Library resource published successfully',
            ]);

        $resource->refresh();
        $this->assertTrue($resource->is_published);
        $this->assertNotNull($resource->published_at);
    }

    // -------------------------------------------------------------------------
    // UNPUBLISH Tests
    // -------------------------------------------------------------------------

    public function test_unpublish_resource_for_admin(): void
    {
        $resource = LibraryResource::factory()->create([
            'is_published' => true,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/library/' . $resource->id . '/unpublish');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Library resource unpublished',
            ]);

        $resource->refresh();
        $this->assertFalse($resource->is_published);
        $this->assertNull($resource->published_at);
    }
}