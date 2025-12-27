<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcademicCalendarEventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_index_returns_all_calendar_events(): void
    {
        AcademicCalendarEvent::factory()->count(3)->create();

        $response = $this->getJson('/api/academic-calendar-events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'title', 'start_date', 'end_date', 'category', 'description'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_index_filters_by_category(): void
    {
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);
        AcademicCalendarEvent::factory()->create(['category' => 'holiday']);
        AcademicCalendarEvent::factory()->create(['category' => 'exam']);

        $response = $this->getJson('/api/academic-calendar-events?category=exam');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_filters_by_date_range(): void
    {
        AcademicCalendarEvent::factory()->create([
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-15',
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-15',
        ]);
        AcademicCalendarEvent::factory()->create([
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-15',
        ]);

        $response = $this->getJson('/api/academic-calendar-events?start_date=2025-01-20&end_date=2025-02-20');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_show_returns_single_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->getJson("/api/academic-calendar-events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'start_date', 'end_date', 'category', 'description'],
            ])
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonPath('data.title', $event->title);
    }

    public function test_show_returns_404_for_nonexistent_event(): void
    {
        $response = $this->getJson('/api/academic-calendar-events/999');

        $response->assertStatus(404);
    }

    public function test_store_creates_new_calendar_event(): void
    {
        $data = [
            'title' => 'Final Exams',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-15',
            'category' => 'exam',
            'description' => 'End of semester final examinations',
        ];

        $response = $this->postJson('/api/academic-calendar-events', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'title', 'start_date', 'end_date', 'category', 'description'],
            ])
            ->assertJsonPath('data.title', 'Final Exams');

        $this->assertDatabaseHas('academic_calendar_events', [
            'title' => 'Final Exams',
            'category' => 'exam',
        ]);
    }

    public function test_store_fails_with_invalid_data(): void
    {
        $data = [
            'title' => '', // required
            'start_date' => 'not-a-date', // must be date
            'end_date' => '', // required
            'category' => '', // required
        ];

        $response = $this->postJson('/api/academic-calendar-events', $data);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    public function test_update_modifies_existing_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create([
            'title' => 'Original Title',
        ]);

        $data = [
            'title' => 'Updated Title',
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-15',
            'category' => 'holiday',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/academic-calendar-events/{$event->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('academic_calendar_events', [
            'id' => $event->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_event(): void
    {
        $data = [
            'title' => 'Updated Title',
            'start_date' => '2025-07-01',
            'end_date' => '2025-07-15',
            'category' => 'holiday',
        ];

        $response = $this->putJson('/api/academic-calendar-events/999', $data);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_calendar_event(): void
    {
        $event = AcademicCalendarEvent::factory()->create();

        $response = $this->deleteJson("/api/academic-calendar-events/{$event->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('academic_calendar_events', [
            'id' => $event->id,
        ]);
    }

    public function test_destroy_returns_404_for_nonexistent_event(): void
    {
        $response = $this->deleteJson('/api/academic-calendar-events/999');

        $response->assertStatus(404);
    }

    public function test_index_returns_empty_array_when_no_events(): void
    {
        $response = $this->getJson('/api/academic-calendar-events');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_index_with_combined_filters(): void
    {
        AcademicCalendarEvent::factory()->create([
            'category' => 'exam',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-15',
        ]);
        AcademicCalendarEvent::factory()->create([
            'category' => 'holiday',
            'start_date' => '2025-01-10',
            'end_date' => '2025-01-20',
        ]);
        AcademicCalendarEvent::factory()->create([
            'category' => 'exam',
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-15',
        ]);

        $response = $this->getJson('/api/academic-calendar-events?category=exam&start_date=2025-01-05&end_date=2025-01-25');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}