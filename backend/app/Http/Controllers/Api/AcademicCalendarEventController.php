<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\AcademicCalendarEventRequest;
use App\Http\Resources\AcademicCalendarEventResource;
use App\Models\AcademicCalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Academic Calendar Event Management API Controller
 *
 * Handles CRUD operations for academic calendar events including:
 * - Event listing with filtering by category and date range
 * - Event creation, retrieval, update, and deletion
 * - Admin and Faculty only for create/update/delete operations
 */
class AcademicCalendarEventController extends ApiController
{
    /**
     * Display a listing of academic calendar events.
     *
     * @queryParam category Filter by event category
     * @queryParam start_date Filter by start date (YYYY-MM-DD)
     * @queryParam end_date Filter by end date (YYYY-MM-DD)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicCalendarEvent::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('start_date', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('end_date', '<=', $request->input('end_date'));
        }

        $events = $query
            ->orderBy('start_date')
            ->get();

        return $this->success(
            AcademicCalendarEventResource::collection($events),
            'Academic calendar events retrieved successfully'
        );
    }

    /**
     * Display the specified academic calendar event.
     */
    public function show(string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);

        return $this->success(
            new AcademicCalendarEventResource($event),
            'Academic calendar event retrieved successfully'
        );
    }

    /**
     * Store a newly created academic calendar event.
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function store(AcademicCalendarEventRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $event = AcademicCalendarEvent::create($validated);

        return $this->created(
            new AcademicCalendarEventResource($event),
            'Academic calendar event created successfully'
        );
    }

    /**
     * Update the specified academic calendar event.
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function update(AcademicCalendarEventRequest $request, string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);
        $validated = $request->validated();

        $event->update($validated);

        return $this->success(
            new AcademicCalendarEventResource($event),
            'Academic calendar event updated successfully'
        );
    }

    /**
     * Remove the specified academic calendar event.
     *
     * Requires admin or faculty role (enforced by route middleware).
     */
    public function destroy(string $id): JsonResponse
    {
        $event = AcademicCalendarEvent::findOrFail($id);
        $event->delete();

        return $this->noContent();
    }
}