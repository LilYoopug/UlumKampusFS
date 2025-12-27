<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\AcademicCalendarEventRequest;
use App\Models\AcademicCalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AcademicCalendarEventController
 *
 * Handles CRUD operations for academic calendar events.
 * Admin and Faculty users can create, update, and delete events.
 * All authenticated users can view events with filtering capabilities.
 */
class AcademicCalendarEventController extends ApiController
{
    /**
     * Display a listing of calendar events.
     *
     * Supports filtering by category and date range via query parameters:
     * - category: Filter by event category (registration, exam, holiday, deadline, event, etc.)
     * - start_date: Filter events starting from this date
     * - end_date: Filter events ending before this date
     * - status: Filter by event status (upcoming, current, past)
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicCalendarEvent::query();

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by status (upcoming, current, past)
        if ($request->has('status')) {
            switch ($request->status) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'current':
                    $query->current();
                    break;
                case 'past':
                    $query->past();
                    break;
            }
        }

        // Order by start date ascending
        $query->orderBy('start_date', 'asc');

        $events = $query->get();

        return $this->success($events);
    }

    /**
     * Store a newly created calendar event.
     *
     * Only Admin and Faculty users can create events.
     *
     * @param AcademicCalendarEventRequest $request
     * @return JsonResponse
     */
    public function store(AcademicCalendarEventRequest $request): JsonResponse
    {
        $event = AcademicCalendarEvent::create($request->validated());

        return $this->created($event, 'Calendar event created successfully');
    }

    /**
     * Display the specified calendar event.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);

        return $this->success($event);
    }

    /**
     * Update the specified calendar event.
     *
     * Only Admin and Faculty users can update events.
     *
     * @param AcademicCalendarEventRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(AcademicCalendarEventRequest $request, string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);

        $event->update($request->validated());

        return $this->success($event, 'Calendar event updated successfully');
    }

    /**
     * Remove the specified calendar event.
     *
     * Only Admin and Faculty users can delete events.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);

        $event->delete();

        return $this->noContent();
    }

    /**
     * Get upcoming calendar events.
     *
     * Returns all events that start today or in the future.
     *
     * @return JsonResponse
     */
    public function upcoming(): JsonResponse
    {
        $events = AcademicCalendarEvent::upcoming()
            ->orderBy('start_date', 'asc')
            ->get();

        return $this->success($events);
    }

    /**
     * Get current/ongoing calendar events.
     *
     * Returns all events that are currently happening.
     *
     * @return JsonResponse
     */
    public function current(): JsonResponse
    {
        $events = AcademicCalendarEvent::current()
            ->orderBy('start_date', 'asc')
            ->get();

        return $this->success($events);
    }

    /**
     * Get available event categories.
     *
     * Returns the list of predefined event categories.
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        return $this->success(AcademicCalendarEvent::getCategories());
    }

    /**
     * Get events by category.
     *
     * @param string $category
     * @return JsonResponse
     */
    public function byCategory(string $category): JsonResponse
    {
        $events = AcademicCalendarEvent::byCategory($category)
            ->orderBy('start_date', 'asc')
            ->get();

        return $this->success($events);
    }

    /**
     * Get events within a date range.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function byDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $events = AcademicCalendarEvent::byDateRange(
            $request->start_date,
            $request->end_date
        )
            ->orderBy('start_date', 'asc')
            ->get();

        return $this->success($events);
    }
}