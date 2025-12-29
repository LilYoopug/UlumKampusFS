<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * User Management API Controller
 *
 * Handles CRUD operations for users including:
 * - User listing with filtering and search
 * - User creation, retrieval, update, and deletion
 * - User profile updates
 */
class UserController extends ApiController
{
    /**
     * Display a listing of users with optional filtering and search.
     *
     * @queryParam role Filter by user role (admin, faculty, student)
     * @queryParam faculty_id Filter by faculty ID
     * @queryParam major_id Filter by major ID
     * @queryParam search Search by name or email
     * @queryParam per_page Items per page (default: 15)
     * @queryParam page Page number (default: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->input('faculty_id'));
        }

        // Filter by major
        if ($request->has('major_id')) {
            $query->where('major_id', $request->input('major_id'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('student_id', 'like', "%{$searchTerm}%");
            });
        }

        // Eager load relationships
        $query->with(['faculty', 'major']);

        // Get all users
        $users = $query->orderBy('name')->get();

        return $this->success(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Hash the password
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Remove password_confirmation if present
        unset($validated['password_confirmation']);

        $user = User::create($validated);

        return $this->created(
            new UserResource($user->load(['faculty', 'major'])),
            'User created successfully'
        );
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['faculty', 'major', 'enrolledCourses'])
            ->findOrFail($id);

        return $this->success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validated();

        // Hash the password if provided
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Remove password fields if not being updated
            unset($validated['password'], $validated['password_confirmation']);
        }

        $user->update($validated);

        return $this->success(
            new UserResource($user->load(['faculty', 'major'])),
            'User updated successfully'
        );
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deleting the currently authenticated user
        if (auth()->id() == $user->id) {
            return $this->error(
                'You cannot delete your own account',
                403
            );
        }

        $user->delete();

        return $this->noContent();
    }

    /**
     * Get the currently authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        $user = auth()->user()->load(['faculty', 'major', 'enrolledCourses']);

        return $this->success(
            new UserResource($user),
            'User profile retrieved successfully'
        );
    }

    /**
     * Update the currently authenticated user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            // Students can update their profile details
            'student_id' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return $this->success(
            new UserResource($user->load(['faculty', 'major'])),
            'Profile updated successfully'
        );
    }

    /**
     * Change the currently authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error(
                'Current password is incorrect',
                422
            );
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->success(
            null,
            'Password changed successfully'
        );
    }

    /**
     * Get users by role.
     */
    public function byRole(string $role): JsonResponse
    {
        if (!in_array($role, ['admin', 'faculty', 'student'])) {
            return $this->error('Invalid role specified', 400);
        }

        $users = User::where('role', $role)
            ->with(['faculty', 'major'])
            ->orderBy('name')
            ->get();

        return $this->success(
            UserResource::collection($users),
            "Users with role '{$role}' retrieved successfully"
        );
    }

    /**
     * Get students by faculty.
     */
    public function byFaculty(string $facultyId): JsonResponse
    {
        $users = User::where('role', 'student')
            ->where('faculty_id', $facultyId)
            ->with(['faculty', 'major'])
            ->orderBy('name')
            ->get();

        return $this->success(
            UserResource::collection($users),
            'Students in this faculty retrieved successfully'
        );
    }

    /**
     * Get students by major.
     */
    public function byMajor(string $majorId): JsonResponse
    {
        $users = User::where('role', 'student')
            ->where('major_id', $majorId)
            ->with(['faculty', 'major'])
            ->orderBy('name')
            ->get();

        return $this->success(
            UserResource::collection($users),
            'Students in this major retrieved successfully'
        );
    }

    /**
     * Get faculty members (admin and faculty roles).
     */
    public function faculty(): JsonResponse
    {
        $users = User::whereIn('role', ['admin', 'faculty'])
            ->with(['faculty', 'major'])
            ->orderBy('name')
            ->get();

        return $this->success(
            UserResource::collection($users),
            'Faculty members retrieved successfully'
        );
    }

    /**
     * Get students only.
     */
    public function students(): JsonResponse
    {
        $users = User::where('role', 'student')
            ->with(['faculty', 'major'])
            ->orderBy('name')
            ->get();

        return $this->success(
            UserResource::collection($users),
            'Students retrieved successfully'
        );
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Prevent deactivating the currently authenticated user
        if (auth()->id() == $user->id) {
            return $this->error(
                'You cannot deactivate your own account',
                403
            );
        }

        $user->update([
            'is_active' => !($user->is_active ?? true),
        ]);

        return $this->success(
            new UserResource($user->load(['faculty', 'major'])),
            'User status updated successfully'
        );
    }

    /**
     * Get the currently authenticated user (alias for frontend getCurrentUser).
     */
    public function user(): JsonResponse
    {
        $user = auth()->user()->load(['faculty', 'major']);

        return $this->success(
            new UserResource($user),
            'Current user retrieved successfully'
        );
    }
}