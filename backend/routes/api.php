<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan rute API untuk aplikasi Anda. Rute-rute
| ini dimuat oleh RouteServiceProvider dan semuanya akan
| ditugaskan ke grup middleware "api".
|
*/

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Webhook for Midtrans payment notifications (no auth required)
Route::post('/payment/notification', [PaymentController::class, 'notificationHandler']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// ============================================================================
// PROTECTED ROUTES (Authentication required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // ------------------------------------------------------------------------
    // User Profile & Authentication
    // ------------------------------------------------------------------------

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------------------------------------------------------------
    // USER MANAGEMENT Routes
    // ------------------------------------------------------------------------

    Route::prefix('users')->group(function () {
        // All authenticated users can view users
        Route::get('/', 'App\Http\Controllers\Api\UserController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\UserController@show');

        // Users can view their own profile and update it
        Route::get('/me/profile', 'App\Http\Controllers\Api\UserController@me');
        Route::put('/me/profile', 'App\Http\Controllers\Api\UserController@updateProfile');
        Route::post('/me/change-password', 'App\Http\Controllers\Api\UserController@changePassword');

        // Filtered user lists (read-only for authenticated users)
        Route::get('/role/{role}', 'App\Http\Controllers\Api\UserController@byRole');
        Route::get('/faculty/{facultyId}', 'App\Http\Controllers\Api\UserController@byFaculty');
        Route::get('/major/{majorId}', 'App\Http\Controllers\Api\UserController@byMajor');
        Route::get('/list/faculty', 'App\Http\Controllers\Api\UserController@faculty');
        Route::get('/list/students', 'App\Http\Controllers\Api\UserController@students');

        // Admin and Faculty only - CRUD operations
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\UserController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\UserController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\UserController@destroy');
            Route::post('/{id}/toggle-status', 'App\Http\Controllers\Api\UserController@toggleStatus');
        });
    });

    // ------------------------------------------------------------------------
    // FACULTY Routes
    // ------------------------------------------------------------------------

    Route::prefix('faculties')->group(function () {
        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\FacultyController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\FacultyController@show');
        Route::get('/{id}/majors', 'App\Http\Controllers\Api\FacultyController@majors');
        Route::get('/{id}/courses', 'App\Http\Controllers\Api\FacultyController@courses');

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\FacultyController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\FacultyController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\FacultyController@destroy');
        });
    });

    // ------------------------------------------------------------------------
    // MAJOR Routes
    // ------------------------------------------------------------------------

    Route::prefix('majors')->group(function () {
        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\MajorController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\MajorController@show');
        Route::get('/{id}/faculty', 'App\Http\Controllers\Api\MajorController@faculty');
        Route::get('/{id}/courses', 'App\Http\Controllers\Api\MajorController@courses');

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\MajorController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\MajorController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\MajorController@destroy');
        });
    });

    // ------------------------------------------------------------------------
    // COURSE Routes
    // ------------------------------------------------------------------------

    Route::prefix('courses')->group(function () {
        // Faculty only - get their courses (must come before /{id} route)
        Route::middleware('role:faculty')->group(function () {
            Route::get('/my-courses', 'App\Http\Controllers\Api\CourseController@myCourses');
        });

        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\CourseController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\CourseController@show');
        Route::get('/{id}/modules', 'App\Http\Controllers\Api\CourseController@modules');
        Route::get('/{id}/enrollments', 'App\Http\Controllers\Api\CourseController@enrollments');
        Route::get('/{id}/students', 'App\Http\Controllers\Api\CourseController@students');
        Route::get('/{id}/assignments', 'App\Http\Controllers\Api\CourseController@assignments');
        Route::get('/{id}/announcements', 'App\Http\Controllers\Api\CourseController@announcements');
        Route::get('/{id}/library-resources', 'App\Http\Controllers\Api\CourseController@libraryResources');
        Route::get('/{id}/discussion-threads', 'App\Http\Controllers\Api\CourseController@discussionThreads');
        Route::get('/{id}/grades', 'App\Http\Controllers\Api\CourseController@grades');

        // Student enrollment
        Route::middleware('role:student')->group(function () {
            Route::post('/{id}/enroll', 'App\Http\Controllers\Api\CourseController@enroll');
            Route::post('/{id}/drop', 'App\Http\Controllers\Api\CourseController@drop');
        });

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\CourseController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\CourseController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\CourseController@destroy');
            Route::post('/{id}/toggle-status', 'App\Http\Controllers\Api\CourseController@toggleStatus');
        });
    });

    // ------------------------------------------------------------------------
    // COURSE MODULE Routes
    // ------------------------------------------------------------------------

    Route::prefix('course-modules')->group(function () {
        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\CourseModuleController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\CourseModuleController@show');
        Route::get('/{id}/assignments', 'App\Http\Controllers\Api\CourseModuleController@assignments');
        Route::get('/{id}/discussions', 'App\Http\Controllers\Api\CourseModuleController@discussions');

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\CourseModuleController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\CourseModuleController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\CourseModuleController@destroy');
        });
    });

    // ------------------------------------------------------------------------
    // COURSE ENROLLMENT Routes
    // ------------------------------------------------------------------------

    Route::prefix('enrollments')->group(function () {
        // Students view their own enrollments
        Route::middleware('role:student')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\EnrollmentController@index');
            Route::get('/{id}', 'App\Http\Controllers\Api\EnrollmentController@show');
        });

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::get('/course/{courseId}', 'App\Http\Controllers\Api\EnrollmentController@byCourse');
            Route::put('/{id}/approve', 'App\Http\Controllers\Api\EnrollmentController@approve');
            Route::put('/{id}/reject', 'App\Http\Controllers\Api\EnrollmentController@reject');
            Route::delete('/{id}', 'App\Http\Controllers\Api\EnrollmentController@destroy');
        });
    });

    // ------------------------------------------------------------------------
    // ASSIGNMENT Routes
    // ------------------------------------------------------------------------

    Route::prefix('assignments')->group(function () {
        // Public (read-only) access for enrolled students
        Route::get('/', 'App\Http\Controllers\Api\AssignmentController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\AssignmentController@show');
        Route::get('/{id}/submissions', 'App\Http\Controllers\Api\AssignmentController@submissions');

        // Student submission
        Route::middleware('role:student')->group(function () {
            Route::post('/{id}/submit', 'App\Http\Controllers\Api\AssignmentController@submit');
            Route::get('/{id}/my-submission', 'App\Http\Controllers\Api\AssignmentController@mySubmission');
        });

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\AssignmentController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\AssignmentController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\AssignmentController@destroy');
            Route::post('/{id}/publish', 'App\Http\Controllers\Api\AssignmentController@publish');
            Route::post('/{id}/unpublish', 'App\Http\Controllers\Api\AssignmentController@unpublish');
        });
    });

    // ------------------------------------------------------------------------
    // ASSIGNMENT SUBMISSION Routes
    // ------------------------------------------------------------------------

    Route::prefix('submissions')->group(function () {
        // Students view their own submissions
        Route::middleware('role:student')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\SubmissionController@index');
            Route::get('/{id}', 'App\Http\Controllers\Api\SubmissionController@show');
            Route::put('/{id}', 'App\Http\Controllers\Api\SubmissionController@update');
        });

        // Admin and Faculty only (grading)
        Route::middleware('role:admin,faculty')->group(function () {
            Route::get('/assignment/{assignmentId}', 'App\Http\Controllers\Api\SubmissionController@byAssignment');
            Route::post('/{id}/grade', 'App\Http\Controllers\Api\SubmissionController@grade');
            Route::post('/{id}/feedback', 'App\Http\Controllers\Api\SubmissionController@feedback');
        });
    });

    // ------------------------------------------------------------------------
    // ANNOUNCEMENT Routes
    // ------------------------------------------------------------------------

    Route::prefix('announcements')->group(function () {
        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\AnnouncementController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\AnnouncementController@show');
        Route::post('/{id}/mark-read', 'App\Http\Controllers\Api\AnnouncementController@markRead');

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\AnnouncementController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\AnnouncementController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\AnnouncementController@destroy');
            Route::post('/{id}/publish', 'App\Http\Controllers\Api\AnnouncementController@publish');
            Route::post('/{id}/unpublish', 'App\Http\Controllers\Api\AnnouncementController@unpublish');
        });
    });

    // ------------------------------------------------------------------------
    // LIBRARY RESOURCE Routes
    // ------------------------------------------------------------------------

    Route::prefix('library')->group(function () {
        // Public (read-only) access for authenticated users
        Route::get('/', 'App\Http\Controllers\Api\LibraryResourceController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\LibraryResourceController@show');
        Route::post('/{id}/download', 'App\Http\Controllers\Api\LibraryResourceController@download');

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\LibraryResourceController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\LibraryResourceController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\LibraryResourceController@destroy');
            Route::post('/{id}/publish', 'App\Http\Controllers\Api\LibraryResourceController@publish');
            Route::post('/{id}/unpublish', 'App\Http\Controllers\Api\LibraryResourceController@unpublish');
        });
    });

    // ------------------------------------------------------------------------
    // DISCUSSION THREAD Routes
    // ------------------------------------------------------------------------

    Route::prefix('discussion-threads')->group(function () {
        // All authenticated users can read discussions
        Route::get('/', 'App\Http\Controllers\Api\DiscussionThreadController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\DiscussionThreadController@show');
        Route::get('/{id}/posts', 'App\Http\Controllers\Api\DiscussionThreadController@posts');

        // Admin and Faculty can create threads
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\DiscussionThreadController@store');
        });

        // Thread owner or admin/faculty can update
        Route::put('/{id}', 'App\Http\Controllers\Api\DiscussionThreadController@update');

        // Thread owner or admin/faculty can delete
        Route::delete('/{id}', 'App\Http\Controllers\Api\DiscussionThreadController@destroy');

        // Thread owner or admin/faculty can close/reopen
        Route::post('/{id}/close', 'App\Http\Controllers\Api\DiscussionThreadController@close');
        Route::post('/{id}/reopen', 'App\Http\Controllers\Api\DiscussionThreadController@reopen');

        // Admin and Faculty moderation
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/{id}/pin', 'App\Http\Controllers\Api\DiscussionThreadController@pin');
            Route::post('/{id}/unpin', 'App\Http\Controllers\Api\DiscussionThreadController@unpin');
            Route::post('/{id}/lock', 'App\Http\Controllers\Api\DiscussionThreadController@lock');
            Route::post('/{id}/unlock', 'App\Http\Controllers\Api\DiscussionThreadController@unlock');
            Route::post('/{id}/archive', 'App\Http\Controllers\Api\DiscussionThreadController@archive');
            Route::post('/{id}/restore', 'App\Http\Controllers\Api\DiscussionThreadController@restore');
        });

        // User-specific routes
        Route::get('/my-threads', 'App\Http\Controllers\Api\DiscussionThreadController@myThreads');
        Route::get('/by-course/{courseId}', 'App\Http\Controllers\Api\DiscussionThreadController@byCourse');
        Route::get('/by-module/{moduleId}', 'App\Http\Controllers\Api\DiscussionThreadController@byModule');
    });

    Route::prefix('discussions')->group(function () {
        // Legacy DiscussionController routes
        Route::get('/', 'App\Http\Controllers\Api\DiscussionController@index');
        Route::get('/threads', 'App\Http\Controllers\Api\DiscussionController@threads');
        Route::get('/threads/{id}', 'App\Http\Controllers\Api\DiscussionController@showThread');
        Route::get('/threads/{id}/posts', 'App\Http\Controllers\Api\DiscussionController@posts');

        // All authenticated users can participate in discussions
        Route::post('/threads', 'App\Http\Controllers\Api\DiscussionController@storeThread');
        Route::post('/threads/{id}/posts', 'App\Http\Controllers\Api\DiscussionController@storePost');
        Route::put('/threads/{id}', 'App\Http\Controllers\Api\DiscussionController@updateThread');
        Route::put('/posts/{id}', 'App\Http\Controllers\Api\DiscussionController@updatePost');
        Route::delete('/threads/{id}', 'App\Http\Controllers\Api\DiscussionController@deleteThread');
        Route::delete('/posts/{id}', 'App\Http\Controllers\Api\DiscussionController@deletePost');
        Route::post('/threads/{id}/like', 'App\Http\Controllers\Api\DiscussionController@likePost');

        // Admin and Faculty moderation
        Route::middleware('role:admin,faculty')->group(function () {
            Route::post('/threads/{id}/pin', 'App\Http\Controllers\Api\DiscussionController@pinThread');
            Route::post('/threads/{id}/unpin', 'App\Http\Controllers\Api\DiscussionController@unpinThread');
            Route::post('/threads/{id}/lock', 'App\Http\Controllers\Api\DiscussionController@lockThread');
            Route::post('/threads/{id}/unlock', 'App\Http\Controllers\Api\DiscussionController@unlockThread');
            Route::post('/threads/{id}/close', 'App\Http\Controllers\Api\DiscussionController@closeThread');
            Route::post('/threads/{id}/reopen', 'App\Http\Controllers\Api\DiscussionController@reopenThread');
            Route::post('/posts/{id}/mark-solution', 'App\Http\Controllers\Api\DiscussionController@markSolution');
            Route::post('/posts/{id}/unmark-solution', 'App\Http\Controllers\Api\DiscussionController@unmarkSolution');
        });
    });

    // ------------------------------------------------------------------------
    // DISCUSSION POST Routes
    // ------------------------------------------------------------------------

    Route::prefix('discussion-threads')->group(function () {
        // All authenticated users can create posts
        Route::post('/{id}/posts', 'App\Http\Controllers\Api\DiscussionPostController@store');
    });

    Route::prefix('discussion-posts')->group(function () {
        // All authenticated users can read posts
        Route::get('/{id}', 'App\Http\Controllers\Api\DiscussionPostController@show');
        Route::get('/{id}/replies', 'App\Http\Controllers\Api\DiscussionPostController@replies');

        // All authenticated users can create replies
        Route::post('/{id}/reply', 'App\Http\Controllers\Api\DiscussionPostController@reply');

        // User-specific routes
        Route::get('/my-posts', 'App\Http\Controllers\Api\DiscussionPostController@myPosts');
        Route::get('/by-thread/{threadId}', 'App\Http\Controllers\Api\DiscussionPostController@byThread');
        Route::get('/solution-by-thread/{threadId}', 'App\Http\Controllers\Api\DiscussionPostController@solutionByThread');

        // Post owners can update their own posts
        Route::put('/{id}', 'App\Http\Controllers\Api\DiscussionPostController@update');

        // Post owners can delete their own posts
        Route::delete('/{id}', 'App\Http\Controllers\Api\DiscussionPostController@destroy');

        // All authenticated users can like posts
        Route::post('/{id}/like', 'App\Http\Controllers\Api\DiscussionPostController@like');
        Route::post('/{id}/unlike', 'App\Http\Controllers\Api\DiscussionPostController@unlike');

        // Solution marking (thread creator or admin)
        Route::post('/{id}/mark-as-solution', 'App\Http\Controllers\Api\DiscussionPostController@markAsSolution');
        Route::post('/{id}/unmark-as-solution', 'App\Http\Controllers\Api\DiscussionPostController@unmarkAsSolution');
    });

    // ------------------------------------------------------------------------
    // NOTIFICATION Routes
    // ------------------------------------------------------------------------

    Route::prefix('notifications')->group(function () {
        // Users manage their own notifications
        Route::get('/', 'App\Http\Controllers\Api\NotificationController@index');
        Route::get('/{id}', 'App\Http\Controllers\Api\NotificationController@show');
        Route::post('/{id}/mark-read', 'App\Http\Controllers\Api\NotificationController@markRead');
        Route::post('/{id}/mark-unread', 'App\Http\Controllers\Api\NotificationController@markUnread');
        Route::post('/mark-all-read', 'App\Http\Controllers\Api\NotificationController@markAllRead');
        Route::get('/unread', 'App\Http\Controllers\Api\NotificationController@unread');
        Route::get('/urgent', 'App\Http\Controllers\Api\NotificationController@urgent');
        Route::get('/counts', 'App\Http\Controllers\Api\NotificationController@counts');
        Route::delete('/{id}', 'App\Http\Controllers\Api\NotificationController@destroy');
        Route::delete('/clear-read', 'App\Http\Controllers\Api\NotificationController@clearRead');

        // Admin only - create and update notifications
        Route::middleware('role:admin')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\NotificationController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\NotificationController@update');
        });
    });

    // ------------------------------------------------------------------------
    // GRADE Routes
    // ------------------------------------------------------------------------

    Route::prefix('grades')->group(function () {
        // Students view their own grades
        Route::middleware('role:student')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\GradeController@index');
            Route::get('/my-grades', 'App\Http\Controllers\Api\GradeController@myGrades');
            Route::get('/{id}', 'App\Http\Controllers\Api\GradeController@show');
        });

        // Admin and Faculty only
        Route::middleware('role:admin,faculty')->group(function () {
            Route::get('/course/{courseId}', 'App\Http\Controllers\Api\GradeController@byCourse');
            Route::get('/assignment/{assignmentId}', 'App\Http\Controllers\Api\GradeController@byAssignment');
            Route::get('/student/{studentId}', 'App\Http\Controllers\Api\GradeController@byStudent');
            Route::post('/', 'App\Http\Controllers\Api\GradeController@store');
            Route::put('/{id}', 'App\Http\Controllers\Api\GradeController@update');
            Route::delete('/{id}', 'App\Http\Controllers\Api\GradeController@destroy');
        });
    });

    // ------------------------------------------------------------------------
    // PAYMENT Routes (existing)
    // ------------------------------------------------------------------------

    Route::prefix('payment')->group(function () {
        Route::post('/create-transaction', [PaymentController::class, 'createTransaction']);
        Route::get('/status/{order_id}', [PaymentController::class, 'checkTransactionStatus']);
    });

    // ------------------------------------------------------------------------
    // PRODUCT Routes (existing)
    // ------------------------------------------------------------------------

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // ------------------------------------------------------------------------
    // ADMIN DASHBOARD Routes
    // ------------------------------------------------------------------------

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome Admin!']);
        });
        Route::get('/stats', 'App\Http\Controllers\Api\AdminController@stats');
        Route::get('/users', 'App\Http\Controllers\Api\AdminController@users');
    });

    // ------------------------------------------------------------------------
    // FACULTY DASHBOARD Routes
    // ------------------------------------------------------------------------

    Route::middleware('role:faculty')->prefix('faculty')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome Faculty!']);
        });
        Route::get('/my-courses', 'App\Http\Controllers\Api\FacultyController@myCourses');
        Route::get('/stats', 'App\Http\Controllers\Api\FacultyController@stats');
    });

    // ------------------------------------------------------------------------
    // STUDENT DASHBOARD Routes
    // ------------------------------------------------------------------------

    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Welcome Student!']);
        });
        Route::get('/my-courses', 'App\Http\Controllers\Api\StudentController@myCourses');
        Route::get('/my-assignments', 'App\Http\Controllers\Api\StudentController@myAssignments');
        Route::get('/my-grades', 'App\Http\Controllers\Api\StudentController@myGrades');
    });
});