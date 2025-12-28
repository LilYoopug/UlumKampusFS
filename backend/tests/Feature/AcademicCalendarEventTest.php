<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarEventTest extends TestCase
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
    // CRUD Operations Tests
    // ============================================

    public function test_authenticated_user_can_list_all_calendar_events(): void
    {
        AcademicCalendarEvent::factory()->count(5)->create();

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'start_date',
                        'end_date',
                        'category',
                        'description',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data);
    }

    public function test_unauthenticated_user_cannot_list_calendar_events(): void
    {
        $response = $this->getJson('/api/calendar-events');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_view_single_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->actingAs($this->student)
            ->getJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_date' => $event->start_date->format('Y-m-d'),
                    'end_date' => $event->end_date->format('Y-m-d'),
                    'category' => $event->category,
                    'description' => $event->description,
                ],
            ]);
    }

    public function test_viewing_nonexistent_event_returns_404(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/999999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_calendar_event(): void
    {
        $eventData = [
            'title' => 'Final Exams',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(14)->format('Y-m-d'),
            'category' => 'exam',
            'description' => 'Final examination period for all courses',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Calendar event created successfully',
                'data' => [
                    'title' => 'Final Exams',
                    'category' => 'exam',
                ],
            ]);

        $this->assertDatabaseHas('academic_calendar_events', [
            'title' => 'Final Exams',
            'category' => 'exam',
        ]);
    }

    public function test_faculty_can_create_calendar_event(): void
    {
        $eventData = [
            'title' => 'Course Registration',
            'start_date' => now()->addDays(30)->format('Y-m-d'),
            'end_date' => now()->addDays(45)->format('Y-m-d'),
            'category' => 'registration',
            'description' => 'Registration period for next semester',
        ];

        $response = $this->actingAs($this->faculty)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('academic_calendar_events', [
            'title' => 'Course Registration',
            'category' => 'registration',
        ]);
    }

    public function test_student_cannot_create_calendar_event(): void
    {
        $eventData = [
            'title' => 'Student Event',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(11)->format('Y-m-d'),
            'category' => 'event',
        ];

        $response = $this->actingAs($this->student)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(403);
    }

    public function test_creating_event_requires_title(): void
    {
        $eventData = [
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(14)->format('Y-m-d'),
            'category' => 'exam',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_creating_event_requires_valid_category(): void
    {
        $eventData = [
            'title' => 'Invalid Event',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(14)->format('Y-m-d'),
            'category' => 'invalid_category',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_creating_event_requires_end_date_after_start_date(): void
    {
        $eventData = [
            'title' => 'Invalid Dates',
            'start_date' => now()->addDays(14)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'category' => 'exam',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_creating_event_requires_start_date_today_or_future(): void
    {
        $eventData = [
            'title' => 'Past Event',
            'start_date' => now()->subDays(7)->format('Y-m-d'),
            'end_date' => now()->subDays(1)->format('Y-m-d'),
            'category' => 'event',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/calendar-events', $eventData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_admin_can_update_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create([
            'title' => 'Original Title',
            'category' => 'event',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'start_date' => $event->start_date->format('Y-m-d'),
            'end_date' => $event->end_date->format('Y-m-d'),
            'category' => 'exam',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/calendar-events/{$event->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Calendar event updated successfully',
                'data' => [
                    'title' => 'Updated Title',
                    'category' => 'exam',
                    'description' => 'Updated description',
                ],
            ]);

        $this->assertDatabaseHas('academic_calendar_events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'category' => 'exam',
        ]);
    }

    public function test_faculty_can_update_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $updateData = [
            'title' => 'Faculty Updated',
            'start_date' => $event->start_date->format('Y-m-d'),
            'end_date' => $event->end_date->format('Y-m-d'),
            'category' => $event->category,
        ];

        $response = $this->actingAs($this->faculty)
            ->putJson("/api/calendar-events/{$event->id}", $updateData);

        $response->assertStatus(200);
    }

    public function test_student_cannot_update_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $updateData = [
            'title' => 'Student Attempt',
            'start_date' => $event->start_date->format('Y-m-d'),
            'end_date' => $event->end_date->format('Y-m-d'),
            'category' => $event->category,
        ];

        $response = $this->actingAs($this->student)
            ->putJson("/api/calendar-events/{$event->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('academic_calendar_events', [
            'id' => $event->id,
        ]);
    }

    public function test_faculty_can_delete_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->actingAs($this->faculty)
            ->deleteJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('academic_calendar_events', [
            'id' => $event->id,
        ]);
    }

    public function test_student_cannot_delete_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->actingAs($this->student)
            ->deleteJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('academic_calendar_events', [
            'id' => $event->id,
        ]);
    }

    // ============================================
    // Filtering and Scopes Tests
    // ============================================

    public function test_can_filter_events_by_category(): void
    {
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);
        AcademicCalendarEvent::factory()->create(['category' => 'holiday']);
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);
        AcademicCalendarEvent::factory()->create(['category' => 'registration']);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events?category=exam');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $event) {
            $this->assertEquals('exam', $event['category']);
        }
    }

    public function test_can_filter_events_by_date_range(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(20),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(25),
            'end_date' => now()->addDays(30),
        ]);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(18)->format('Y-m-d');

        $response = $this->actingAs($this->student)
            ->getJson("/api/calendar-events?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_can_filter_events_by_upcoming_status(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(20),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events?status=upcoming');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_filter_events_by_current_status(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now(),
            'end_date' => now()->addDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events?status=current');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_can_filter_events_by_past_status(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(20),
            'end_date' => now()->subDays(15),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events?status=past');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    // ============================================
    // Special Endpoint Tests
    // ============================================

    public function test_can_get_upcoming_events(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(20),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/upcoming');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_can_get_current_events(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now(),
            'end_date' => now()->addDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/current');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_can_get_event_categories(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/categories');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $expectedCategories = array_keys(AcademicCalendarEvent::getCategories());
        $this->assertEquals($expectedCategories, $data);
    }

    public function test_can_get_events_by_specific_category(): void
    {
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);
        AcademicCalendarEvent::factory()->create(['category' => 'holiday']);
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/category/exam');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        foreach ($data as $event) {
            $this->assertEquals('exam', $event['category']);
        }
    }

    public function test_can_get_events_by_date_range_endpoint(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(20),
        ]);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(18)->format('Y-m-d');

        $response = $this->actingAs($this->student)
            ->getJson("/api/calendar-events/by-date-range?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_date_range_endpoint_requires_valid_dates(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events/by-date-range?start_date=invalid&end_date=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_date_range_endpoint_requires_end_date_after_start_date(): void
    {
        $startDate = now()->addDays(10)->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->student)
            ->getJson("/api/calendar-events/by-date-range?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    // ============================================
    // Model Scopes Tests
    // ============================================

    public function test_model_scope_by_category_filters_correctly(): void
    {
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);
        AcademicCalendarEvent::factory()->create(['category' => 'holiday']);
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);

        $events = AcademicCalendarEvent::byCategory('exam')->get();

        $this->assertCount(2, $events);
        foreach ($events as $event) {
            $this->assertEquals('exam', $event->category);
        }
    }

    public function test_model_scope_by_date_range_filters_correctly(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(20),
            'end_date' => now()->addDays(25),
        ]);

        $events = AcademicCalendarEvent::byDateRange(
            now()->addDays(1)->format('Y-m-d'),
            now()->addDays(15)->format('Y-m-d')
        )->get();

        $this->assertCount(1, $events);
    }

    public function test_model_scope_upcoming_filters_correctly(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $events = AcademicCalendarEvent::upcoming()->get();

        $this->assertCount(1, $events);
    }

    public function test_model_scope_past_filters_correctly(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $events = AcademicCalendarEvent::past()->get();

        $this->assertCount(1, $events);
    }

    public function test_model_scope_current_filters_correctly(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $events = AcademicCalendarEvent::current()->get();

        $this->assertCount(1, $events);
    }

    public function test_model_get_categories_returns_all_categories(): void
    {
        $categories = AcademicCalendarEvent::getCategories();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('registration', $categories);
        $this->assertArrayHasKey('exam', $categories);
        $this->assertArrayHasKey('holiday', $categories);
        $this->assertArrayHasKey('deadline', $categories);
        $this->assertArrayHasKey('event', $categories);
        $this->assertArrayHasKey('orientation', $categories);
        $this->assertArrayHasKey('graduation', $categories);
        $this->assertArrayHasKey('other', $categories);
    }

    // ============================================
    // Response Structure Tests
    // ============================================

    public function test_event_resource_returns_correct_structure(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->actingAs($this->student)
            ->getJson("/api/calendar-events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'start_date',
                    'end_date',
                    'category',
                    'description',
                ],
            ]);
    }

    public function test_events_are_ordered_by_start_date(): void
    {
        $event3 = AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(20),
        ]);
        $event1 = AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(5),
        ]);
        $event2 = AcademicCalendarEvent::factory()->create([
            'start_date' => now()->addDays(10),
        ]);

        $response = $this->actingAs($this->student)
            ->getJson('/api/calendar-events');

        $data = $response->json('data');
        $this->assertEquals($event1->id, $data[0]['id']);
        $this->assertEquals($event2->id, $data[1]['id']);
        $this->assertEquals($event3->id, $data[2]['id']);
    }
}