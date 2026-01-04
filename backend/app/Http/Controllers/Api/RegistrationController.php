<?php

namespace App\Http\Controllers\Api;

use App\Models\StudentRegistration;
use App\Models\User;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends ApiController
{
    /**
     * Get current user's registration data.
     */
    public function getMyRegistration(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        $registration = StudentRegistration::where('user_id', $user->id)
            ->with(['firstChoice', 'secondChoice', 'reviewer'])
            ->first();

        if (!$registration) {
            return $this->success(null, 'No registration found. You can create a new registration.');
        }

        return $this->success($this->formatRegistrationData($registration), 'Registration data retrieved successfully');
    }

    /**
     * Submit registration data (only final submission, no drafts).
     */
    public function saveRegistration(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        $validated = $request->validate([
            // Informasi Pribadi
            'nisn' => 'required|string|max:20',
            'nik' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'religion' => 'nullable|string|max:50',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'citizenship' => 'nullable|string|max:50',
            'parent_name' => 'required|string|max:100',
            'parent_phone' => 'required|string|max:20',
            'parent_job' => 'nullable|string|max:100',

            // Informasi Pendidikan
            'school_name' => 'required|string|max:200',
            'school_address' => 'required|string',
            'graduation_year_school' => 'required|integer|min:1900|max:2100',
            'school_type' => 'required|in:SMA,SMK,MA,Lainnya',
            'school_major' => 'required|string|max:100',
            'average_grade' => 'required|numeric|min:0|max:100',

            // Preferensi
            'first_choice_id' => 'required|string|exists:majors,code',
            'second_choice_id' => 'nullable|string|exists:majors,code',

            // Documents (array of file paths)
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|string|max:500',
        ]);

        // Check if user already has a registration
        $existingRegistration = StudentRegistration::where('user_id', $user->id)->first();
        
        if ($existingRegistration) {
            // Cannot modify if already submitted
            if ($existingRegistration->status !== 'rejected') {
                return $this->error('Registration already exists and cannot be modified', 400);
            }
            
            // Update rejected registration
            $registration = $existingRegistration;
        } else {
            // Create new registration
            $registration = new StudentRegistration();
            $registration->user_id = $user->id;
        }

        // Fill registration data
        $registration->fill($validated);

        // Handle document paths
        if (isset($validated['documents'])) {
            $registration->documents = $validated['documents'];
        }

        // Set as submitted
        $registration->status = 'submitted';
        $registration->submitted_at = now();
        $registration->rejection_reason = null;

        $registration->save();

        return $this->success(
            $this->formatRegistrationData($registration->load(['firstChoice', 'secondChoice', 'reviewer'])),
            'Registration submitted successfully'
        );
    }

    /**
     * Get all registrations (for admin/staff).
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        // Only admin and staff can view all registrations
        if (!in_array($user->role, ['admin', 'staff'])) {
            return $this->forbidden('You do not have permission to view all registrations');
        }

        $query = StudentRegistration::with(['user', 'firstChoice', 'secondChoice', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by major preference
        if ($request->has('first_choice_id')) {
            $query->where('first_choice_id', $request->first_choice_id);
        }

        // Search by name or student ID
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        return $this->success($registrations, 'Registrations retrieved successfully');
    }

    /**
     * Get registration details (for admin/staff).
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        // Only admin and staff can view registration details
        if (!in_array($user->role, ['admin', 'staff'])) {
            return $this->forbidden('You do not have permission to view registration details');
        }

        $registration = StudentRegistration::with(['user', 'firstChoice', 'secondChoice', 'reviewer'])
            ->findOrFail($id);

        return $this->success($this->formatRegistrationData($registration), 'Registration details retrieved successfully');
    }

    /**
     * Review registration (accept/reject) - for admin/staff.
     */
    public function review(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return $this->unauthorized('User not authenticated');
        }

        // Only admin and staff can review registrations
        if (!in_array($user->role, ['admin', 'staff'])) {
            return $this->forbidden('You do not have permission to review registrations');
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        $registration = StudentRegistration::findOrFail($id);

        // Can only review submitted or under_review registrations
        if (!in_array($registration->status, ['submitted', 'under_review'])) {
            return $this->error('Registration cannot be reviewed in current status', 400);
        }

        $registration->status = $validated['status'];
        $registration->reviewed_by = $user->id;
        $registration->reviewed_at = now();

        if ($validated['status'] === 'rejected') {
            $registration->rejection_reason = $validated['rejection_reason'];
        } else {
            // If accepted, update user's faculty and major
            $registration->user->faculty_id = $registration->firstChoice->faculty_id;
            $registration->user->major_id = $registration->first_choice_id;
            $registration->user->role = 'student';
            $registration->user->save();
        }

        $registration->save();

        return $this->success(
            $this->formatRegistrationData($registration->load(['firstChoice', 'secondChoice', 'reviewer'])),
            "Registration {$validated['status']} successfully"
        );
    }

    /**
     * Format registration data for response.
     */
    private function formatRegistrationData(StudentRegistration $registration): array
    {
        return [
            'id' => $registration->id,
            'user_id' => $registration->user_id,
            'user_name' => $registration->user->name ?? null,
            'user_email' => $registration->user->email ?? null,
            'student_id' => $registration->user->student_id ?? null,
            
            // Informasi Pribadi
            'nisn' => $registration->nisn,
            'nik' => $registration->nik,
            'date_of_birth' => $registration->date_of_birth,
            'place_of_birth' => $registration->place_of_birth,
            'gender' => $registration->gender,
            'religion' => $registration->religion,
            'address' => $registration->address,
            'city' => $registration->city,
            'postal_code' => $registration->postal_code,
            'citizenship' => $registration->citizenship,
            'parent_name' => $registration->parent_name,
            'parent_phone' => $registration->parent_phone,
            'parent_job' => $registration->parent_job,

            // Informasi Pendidikan
            'school_name' => $registration->school_name,
            'school_address' => $registration->school_address,
            'graduation_year_school' => $registration->graduation_year_school,
            'school_type' => $registration->school_type,
            'school_major' => $registration->school_major,
            'average_grade' => $registration->average_grade,

            // Preferensi
            'first_choice_id' => $registration->first_choice_id,
            'first_choice_name' => $registration->firstChoice->name ?? null,
            'second_choice_id' => $registration->second_choice_id,
            'second_choice_name' => $registration->secondChoice->name ?? null,

            // Status & Review
            'status' => $registration->status,
            'submitted_at' => $registration->submitted_at,
            'documents' => $registration->documents,
            'rejection_reason' => $registration->rejection_reason,
            'reviewed_by' => $registration->reviewed_by,
            'reviewer_name' => $registration->reviewer->name ?? null,
            'reviewed_at' => $registration->reviewed_at,

            'created_at' => $registration->created_at,
            'updated_at' => $registration->updated_at,
        ];
    }
}
