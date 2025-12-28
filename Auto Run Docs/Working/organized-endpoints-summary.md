# API Endpoints Summary

Total API Endpoints: 315

## Resources

### academic-calendar-events

#### DELETE

- `api/academic-calendar-events/{id}`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@destroy`
  - Roles: admin, faculty

#### GET

- `api/academic-calendar-events`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@index`

- `api/academic-calendar-events/{id}`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@show`

#### HEAD

- `api/academic-calendar-events`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@index`

- `api/academic-calendar-events/{id}`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@show`

#### POST

- `api/academic-calendar-events`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@store`
  - Roles: admin, faculty

#### PUT

- `api/academic-calendar-events/{id}`
  - Action: `App\Http\Controllers\Api\AcademicCalendarEventController@update`
  - Roles: admin, faculty

### admin

#### GET

- `api/admin/dashboard`
  - Action: `Closure`
  - Roles: admin

- `api/admin/stats`
  - Action: `App\Http\Controllers\Api\AdminController@stats`
  - Roles: admin

- `api/admin/users`
  - Action: `App\Http\Controllers\Api\AdminController@users`
  - Roles: admin

#### HEAD

- `api/admin/dashboard`
  - Action: `Closure`
  - Roles: admin

- `api/admin/stats`
  - Action: `App\Http\Controllers\Api\AdminController@stats`
  - Roles: admin

- `api/admin/users`
  - Action: `App\Http\Controllers\Api\AdminController@users`
  - Roles: admin

### announcements

#### DELETE

- `api/announcements/{id}`
  - Action: `App\Http\Controllers\Api\AnnouncementController@destroy`
  - Roles: admin, faculty

#### GET

- `api/announcements`
  - Action: `App\Http\Controllers\Api\AnnouncementController@index`

- `api/announcements/{id}`
  - Action: `App\Http\Controllers\Api\AnnouncementController@show`

#### HEAD

- `api/announcements`
  - Action: `App\Http\Controllers\Api\AnnouncementController@index`

- `api/announcements/{id}`
  - Action: `App\Http\Controllers\Api\AnnouncementController@show`

#### POST

- `api/announcements`
  - Action: `App\Http\Controllers\Api\AnnouncementController@store`
  - Roles: admin, faculty

- `api/announcements/{id}/mark-read`
  - Action: `App\Http\Controllers\Api\AnnouncementController@markRead`

- `api/announcements/{id}/publish`
  - Action: `App\Http\Controllers\Api\AnnouncementController@publish`
  - Roles: admin, faculty

- `api/announcements/{id}/unpublish`
  - Action: `App\Http\Controllers\Api\AnnouncementController@unpublish`
  - Roles: admin, faculty

#### PUT

- `api/announcements/{id}`
  - Action: `App\Http\Controllers\Api\AnnouncementController@update`
  - Roles: admin, faculty

### assignments

#### DELETE

- `api/assignments/{id}`
  - Action: `App\Http\Controllers\Api\AssignmentController@destroy`
  - Roles: admin, faculty

#### GET

- `api/assignments`
  - Action: `App\Http\Controllers\Api\AssignmentController@index`

- `api/assignments/{id}`
  - Action: `App\Http\Controllers\Api\AssignmentController@show`

- `api/assignments/{id}/my-submission`
  - Action: `App\Http\Controllers\Api\AssignmentController@mySubmission`
  - Roles: student

- `api/assignments/{id}/submissions`
  - Action: `App\Http\Controllers\Api\AssignmentController@submissions`

#### HEAD

- `api/assignments`
  - Action: `App\Http\Controllers\Api\AssignmentController@index`

- `api/assignments/{id}`
  - Action: `App\Http\Controllers\Api\AssignmentController@show`

- `api/assignments/{id}/my-submission`
  - Action: `App\Http\Controllers\Api\AssignmentController@mySubmission`
  - Roles: student

- `api/assignments/{id}/submissions`
  - Action: `App\Http\Controllers\Api\AssignmentController@submissions`

#### POST

- `api/assignments`
  - Action: `App\Http\Controllers\Api\AssignmentController@store`
  - Roles: admin, faculty

- `api/assignments/{id}/publish`
  - Action: `App\Http\Controllers\Api\AssignmentController@publish`
  - Roles: admin, faculty

- `api/assignments/{id}/submit`
  - Action: `App\Http\Controllers\Api\AssignmentController@submit`
  - Roles: student

- `api/assignments/{id}/unpublish`
  - Action: `App\Http\Controllers\Api\AssignmentController@unpublish`
  - Roles: admin, faculty

#### PUT

- `api/assignments/{id}`
  - Action: `App\Http\Controllers\Api\AssignmentController@update`
  - Roles: admin, faculty

### course-modules

#### DELETE

- `api/course-modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@destroy`
  - Roles: admin, faculty

#### GET

- `api/course-modules`
  - Action: `App\Http\Controllers\Api\CourseModuleController@index`

- `api/course-modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@show`

- `api/course-modules/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseModuleController@assignments`

- `api/course-modules/{id}/discussions`
  - Action: `App\Http\Controllers\Api\CourseModuleController@discussions`

#### HEAD

- `api/course-modules`
  - Action: `App\Http\Controllers\Api\CourseModuleController@index`

- `api/course-modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@show`

- `api/course-modules/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseModuleController@assignments`

- `api/course-modules/{id}/discussions`
  - Action: `App\Http\Controllers\Api\CourseModuleController@discussions`

#### POST

- `api/course-modules`
  - Action: `App\Http\Controllers\Api\CourseModuleController@store`
  - Roles: admin, faculty

#### PUT

- `api/course-modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@update`
  - Roles: admin, faculty

### courses

#### DELETE

- `api/courses/{id}`
  - Action: `App\Http\Controllers\Api\CourseController@destroy`
  - Roles: admin, faculty

#### GET

- `api/courses`
  - Action: `App\Http\Controllers\Api\CourseController@index`

- `api/courses/my-courses`
  - Action: `App\Http\Controllers\Api\CourseController@myCourses`
  - Roles: faculty

- `api/courses/{id}`
  - Action: `App\Http\Controllers\Api\CourseController@show`

- `api/courses/{id}/announcements`
  - Action: `App\Http\Controllers\Api\CourseController@announcements`

- `api/courses/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseController@assignments`

- `api/courses/{id}/discussion-threads`
  - Action: `App\Http\Controllers\Api\CourseController@discussionThreads`

- `api/courses/{id}/enrollments`
  - Action: `App\Http\Controllers\Api\CourseController@enrollments`

- `api/courses/{id}/grades`
  - Action: `App\Http\Controllers\Api\CourseController@grades`

- `api/courses/{id}/library-resources`
  - Action: `App\Http\Controllers\Api\CourseController@libraryResources`

- `api/courses/{id}/modules`
  - Action: `App\Http\Controllers\Api\CourseController@modules`

- `api/courses/{id}/students`
  - Action: `App\Http\Controllers\Api\CourseController@students`

#### HEAD

- `api/courses`
  - Action: `App\Http\Controllers\Api\CourseController@index`

- `api/courses/my-courses`
  - Action: `App\Http\Controllers\Api\CourseController@myCourses`
  - Roles: faculty

- `api/courses/{id}`
  - Action: `App\Http\Controllers\Api\CourseController@show`

- `api/courses/{id}/announcements`
  - Action: `App\Http\Controllers\Api\CourseController@announcements`

- `api/courses/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseController@assignments`

- `api/courses/{id}/discussion-threads`
  - Action: `App\Http\Controllers\Api\CourseController@discussionThreads`

- `api/courses/{id}/enrollments`
  - Action: `App\Http\Controllers\Api\CourseController@enrollments`

- `api/courses/{id}/grades`
  - Action: `App\Http\Controllers\Api\CourseController@grades`

- `api/courses/{id}/library-resources`
  - Action: `App\Http\Controllers\Api\CourseController@libraryResources`

- `api/courses/{id}/modules`
  - Action: `App\Http\Controllers\Api\CourseController@modules`

- `api/courses/{id}/students`
  - Action: `App\Http\Controllers\Api\CourseController@students`

#### POST

- `api/courses`
  - Action: `App\Http\Controllers\Api\CourseController@store`
  - Roles: admin, faculty

- `api/courses/{id}/drop`
  - Action: `App\Http\Controllers\Api\CourseController@drop`
  - Roles: student

- `api/courses/{id}/enroll`
  - Action: `App\Http\Controllers\Api\CourseController@enroll`
  - Roles: student

- `api/courses/{id}/modules`
  - Action: `App\Http\Controllers\Api\CourseModuleController@store`
  - Roles: admin, faculty

- `api/courses/{id}/modules/reorder`
  - Action: `App\Http\Controllers\Api\CourseModuleController@reorder`
  - Roles: admin, faculty

- `api/courses/{id}/toggle-status`
  - Action: `App\Http\Controllers\Api\CourseController@toggleStatus`
  - Roles: admin, faculty

#### PUT

- `api/courses/{id}`
  - Action: `App\Http\Controllers\Api\CourseController@update`
  - Roles: admin, faculty

### dashboard

#### GET

- `api/dashboard`
  - Action: `App\Http\Controllers\Api\DashboardController@index`

- `api/dashboard/dosen/{instructorName}`
  - Action: `App\Http\Controllers\Api\DashboardController@dosenStats`

- `api/dashboard/enrollment-trends`
  - Action: `App\Http\Controllers\Api\DashboardController@enrollmentTrends`

- `api/dashboard/faculty`
  - Action: `App\Http\Controllers\Api\DashboardController@facultyStats`
  - Roles: faculty

- `api/dashboard/faculty-enrollment`
  - Action: `App\Http\Controllers\Api\DashboardController@facultyEnrollment`

- `api/dashboard/grade-distribution`
  - Action: `App\Http\Controllers\Api\DashboardController@gradeDistribution`

- `api/dashboard/management`
  - Action: `App\Http\Controllers\Api\DashboardController@managementStats`
  - Roles: admin

- `api/dashboard/prodi`
  - Action: `App\Http\Controllers\Api\DashboardController@prodiStats`
  - Roles: admin, faculty

- `api/dashboard/student`
  - Action: `App\Http\Controllers\Api\DashboardController@studentStats`
  - Roles: student

#### HEAD

- `api/dashboard`
  - Action: `App\Http\Controllers\Api\DashboardController@index`

- `api/dashboard/dosen/{instructorName}`
  - Action: `App\Http\Controllers\Api\DashboardController@dosenStats`

- `api/dashboard/enrollment-trends`
  - Action: `App\Http\Controllers\Api\DashboardController@enrollmentTrends`

- `api/dashboard/faculty`
  - Action: `App\Http\Controllers\Api\DashboardController@facultyStats`
  - Roles: faculty

- `api/dashboard/faculty-enrollment`
  - Action: `App\Http\Controllers\Api\DashboardController@facultyEnrollment`

- `api/dashboard/grade-distribution`
  - Action: `App\Http\Controllers\Api\DashboardController@gradeDistribution`

- `api/dashboard/management`
  - Action: `App\Http\Controllers\Api\DashboardController@managementStats`
  - Roles: admin

- `api/dashboard/prodi`
  - Action: `App\Http\Controllers\Api\DashboardController@prodiStats`
  - Roles: admin, faculty

- `api/dashboard/student`
  - Action: `App\Http\Controllers\Api\DashboardController@studentStats`
  - Roles: student

### discussion-posts

#### DELETE

- `api/discussion-posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@destroy`

#### GET

- `api/discussion-posts/by-thread/{threadId}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@byThread`

- `api/discussion-posts/my-posts`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@myPosts`

- `api/discussion-posts/solution-by-thread/{threadId}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@solutionByThread`

- `api/discussion-posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@show`

- `api/discussion-posts/{id}/replies`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@replies`

#### HEAD

- `api/discussion-posts/by-thread/{threadId}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@byThread`

- `api/discussion-posts/my-posts`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@myPosts`

- `api/discussion-posts/solution-by-thread/{threadId}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@solutionByThread`

- `api/discussion-posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@show`

- `api/discussion-posts/{id}/replies`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@replies`

#### POST

- `api/discussion-posts/{id}/like`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@like`

- `api/discussion-posts/{id}/mark-as-solution`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@markAsSolution`

- `api/discussion-posts/{id}/reply`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@reply`

- `api/discussion-posts/{id}/unlike`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@unlike`

- `api/discussion-posts/{id}/unmark-as-solution`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@unmarkAsSolution`

#### PUT

- `api/discussion-posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@update`

### discussion-threads

#### DELETE

- `api/discussion-threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@destroy`

#### GET

- `api/discussion-threads`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@index`

- `api/discussion-threads/by-course/{courseId}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@byCourse`

- `api/discussion-threads/by-module/{moduleId}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@byModule`

- `api/discussion-threads/my-threads`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@myThreads`

- `api/discussion-threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@show`

- `api/discussion-threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@posts`

#### HEAD

- `api/discussion-threads`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@index`

- `api/discussion-threads/by-course/{courseId}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@byCourse`

- `api/discussion-threads/by-module/{moduleId}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@byModule`

- `api/discussion-threads/my-threads`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@myThreads`

- `api/discussion-threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@show`

- `api/discussion-threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@posts`

#### POST

- `api/discussion-threads`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@store`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/archive`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@archive`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/close`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@close`

- `api/discussion-threads/{id}/lock`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@lock`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/pin`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@pin`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionPostController@store`

- `api/discussion-threads/{id}/reopen`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@reopen`

- `api/discussion-threads/{id}/restore`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@restore`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/unlock`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@unlock`
  - Roles: admin, faculty

- `api/discussion-threads/{id}/unpin`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@unpin`
  - Roles: admin, faculty

#### PUT

- `api/discussion-threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionThreadController@update`

### discussions

#### DELETE

- `api/discussions/posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@deletePost`

- `api/discussions/threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@deleteThread`

#### GET

- `api/discussions`
  - Action: `App\Http\Controllers\Api\DiscussionController@index`

- `api/discussions/threads`
  - Action: `App\Http\Controllers\Api\DiscussionController@threads`

- `api/discussions/threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@showThread`

- `api/discussions/threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionController@posts`

#### HEAD

- `api/discussions`
  - Action: `App\Http\Controllers\Api\DiscussionController@index`

- `api/discussions/threads`
  - Action: `App\Http\Controllers\Api\DiscussionController@threads`

- `api/discussions/threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@showThread`

- `api/discussions/threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionController@posts`

#### POST

- `api/discussions/posts/{id}/mark-solution`
  - Action: `App\Http\Controllers\Api\DiscussionController@markSolution`
  - Roles: admin, faculty

- `api/discussions/posts/{id}/unmark-solution`
  - Action: `App\Http\Controllers\Api\DiscussionController@unmarkSolution`
  - Roles: admin, faculty

- `api/discussions/threads`
  - Action: `App\Http\Controllers\Api\DiscussionController@storeThread`

- `api/discussions/threads/{id}/close`
  - Action: `App\Http\Controllers\Api\DiscussionController@closeThread`
  - Roles: admin, faculty

- `api/discussions/threads/{id}/like`
  - Action: `App\Http\Controllers\Api\DiscussionController@likePost`

- `api/discussions/threads/{id}/lock`
  - Action: `App\Http\Controllers\Api\DiscussionController@lockThread`
  - Roles: admin, faculty

- `api/discussions/threads/{id}/pin`
  - Action: `App\Http\Controllers\Api\DiscussionController@pinThread`
  - Roles: admin, faculty

- `api/discussions/threads/{id}/posts`
  - Action: `App\Http\Controllers\Api\DiscussionController@storePost`

- `api/discussions/threads/{id}/reopen`
  - Action: `App\Http\Controllers\Api\DiscussionController@reopenThread`
  - Roles: admin, faculty

- `api/discussions/threads/{id}/unlock`
  - Action: `App\Http\Controllers\Api\DiscussionController@unlockThread`
  - Roles: admin, faculty

- `api/discussions/threads/{id}/unpin`
  - Action: `App\Http\Controllers\Api\DiscussionController@unpinThread`
  - Roles: admin, faculty

#### PUT

- `api/discussions/posts/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@updatePost`

- `api/discussions/threads/{id}`
  - Action: `App\Http\Controllers\Api\DiscussionController@updateThread`

### enrollments

#### DELETE

- `api/enrollments/{id}`
  - Action: `App\Http\Controllers\Api\EnrollmentController@destroy`
  - Roles: admin, faculty

#### GET

- `api/enrollments`
  - Action: `App\Http\Controllers\Api\EnrollmentController@index`
  - Roles: student

- `api/enrollments/course/{courseId}`
  - Action: `App\Http\Controllers\Api\EnrollmentController@byCourse`
  - Roles: admin, faculty

- `api/enrollments/{id}`
  - Action: `App\Http\Controllers\Api\EnrollmentController@show`
  - Roles: student

#### HEAD

- `api/enrollments`
  - Action: `App\Http\Controllers\Api\EnrollmentController@index`
  - Roles: student

- `api/enrollments/course/{courseId}`
  - Action: `App\Http\Controllers\Api\EnrollmentController@byCourse`
  - Roles: admin, faculty

- `api/enrollments/{id}`
  - Action: `App\Http\Controllers\Api\EnrollmentController@show`
  - Roles: student

#### PUT

- `api/enrollments/{id}/approve`
  - Action: `App\Http\Controllers\Api\EnrollmentController@approve`
  - Roles: admin, faculty

- `api/enrollments/{id}/reject`
  - Action: `App\Http\Controllers\Api\EnrollmentController@reject`
  - Roles: admin, faculty

### faculties

#### DELETE

- `api/faculties/{id}`
  - Action: `App\Http\Controllers\Api\FacultyController@destroy`
  - Roles: admin, faculty

#### GET

- `api/faculties`
  - Action: `App\Http\Controllers\Api\FacultyController@index`

- `api/faculties/{id}`
  - Action: `App\Http\Controllers\Api\FacultyController@show`

- `api/faculties/{id}/courses`
  - Action: `App\Http\Controllers\Api\FacultyController@courses`

- `api/faculties/{id}/majors`
  - Action: `App\Http\Controllers\Api\FacultyController@majors`

#### HEAD

- `api/faculties`
  - Action: `App\Http\Controllers\Api\FacultyController@index`

- `api/faculties/{id}`
  - Action: `App\Http\Controllers\Api\FacultyController@show`

- `api/faculties/{id}/courses`
  - Action: `App\Http\Controllers\Api\FacultyController@courses`

- `api/faculties/{id}/majors`
  - Action: `App\Http\Controllers\Api\FacultyController@majors`

#### POST

- `api/faculties`
  - Action: `App\Http\Controllers\Api\FacultyController@store`
  - Roles: admin, faculty

#### PUT

- `api/faculties/{id}`
  - Action: `App\Http\Controllers\Api\FacultyController@update`
  - Roles: admin, faculty

### faculty

#### GET

- `api/faculty/dashboard`
  - Action: `Closure`
  - Roles: faculty

- `api/faculty/my-courses`
  - Action: `App\Http\Controllers\Api\FacultyController@myCourses`
  - Roles: faculty

- `api/faculty/stats`
  - Action: `App\Http\Controllers\Api\FacultyController@stats`
  - Roles: faculty

#### HEAD

- `api/faculty/dashboard`
  - Action: `Closure`
  - Roles: faculty

- `api/faculty/my-courses`
  - Action: `App\Http\Controllers\Api\FacultyController@myCourses`
  - Roles: faculty

- `api/faculty/stats`
  - Action: `App\Http\Controllers\Api\FacultyController@stats`
  - Roles: faculty

### forgot-password

#### POST

- `api/forgot-password`
  - Action: `App\Http\Controllers\Api\Auth\AuthController@forgotPassword`

### grades

#### DELETE

- `api/grades/{id}`
  - Action: `App\Http\Controllers\Api\GradeController@destroy`
  - Roles: admin, faculty

#### GET

- `api/grades`
  - Action: `App\Http\Controllers\Api\GradeController@index`
  - Roles: student

- `api/grades/analytics/course`
  - Action: `App\Http\Controllers\Api\GradeController@analyticsByCourse`
  - Roles: admin, faculty

- `api/grades/analytics/faculty`
  - Action: `App\Http\Controllers\Api\GradeController@analyticsByFaculty`
  - Roles: admin, faculty

- `api/grades/assignment/{assignmentId}`
  - Action: `App\Http\Controllers\Api\GradeController@byAssignment`
  - Roles: admin, faculty

- `api/grades/course/{courseId}`
  - Action: `App\Http\Controllers\Api\GradeController@byCourse`
  - Roles: admin, faculty

- `api/grades/distribution/{courseId}`
  - Action: `App\Http\Controllers\Api\GradeController@distribution`
  - Roles: admin, faculty

- `api/grades/my-grades`
  - Action: `App\Http\Controllers\Api\GradeController@myGrades`
  - Roles: student

- `api/grades/student/{studentId}`
  - Action: `App\Http\Controllers\Api\GradeController@byStudent`
  - Roles: admin, faculty

- `api/grades/{id}`
  - Action: `App\Http\Controllers\Api\GradeController@show`
  - Roles: student

#### HEAD

- `api/grades`
  - Action: `App\Http\Controllers\Api\GradeController@index`
  - Roles: student

- `api/grades/analytics/course`
  - Action: `App\Http\Controllers\Api\GradeController@analyticsByCourse`
  - Roles: admin, faculty

- `api/grades/analytics/faculty`
  - Action: `App\Http\Controllers\Api\GradeController@analyticsByFaculty`
  - Roles: admin, faculty

- `api/grades/assignment/{assignmentId}`
  - Action: `App\Http\Controllers\Api\GradeController@byAssignment`
  - Roles: admin, faculty

- `api/grades/course/{courseId}`
  - Action: `App\Http\Controllers\Api\GradeController@byCourse`
  - Roles: admin, faculty

- `api/grades/distribution/{courseId}`
  - Action: `App\Http\Controllers\Api\GradeController@distribution`
  - Roles: admin, faculty

- `api/grades/my-grades`
  - Action: `App\Http\Controllers\Api\GradeController@myGrades`
  - Roles: student

- `api/grades/student/{studentId}`
  - Action: `App\Http\Controllers\Api\GradeController@byStudent`
  - Roles: admin, faculty

- `api/grades/{id}`
  - Action: `App\Http\Controllers\Api\GradeController@show`
  - Roles: student

#### POST

- `api/grades`
  - Action: `App\Http\Controllers\Api\GradeController@store`
  - Roles: admin, faculty

#### PUT

- `api/grades/{id}`
  - Action: `App\Http\Controllers\Api\GradeController@update`
  - Roles: admin, faculty

### health

#### GET

- `api/health`
  - Action: `Closure`

#### HEAD

- `api/health`
  - Action: `Closure`

### library

#### DELETE

- `api/library/{id}`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@destroy`
  - Roles: admin, faculty

#### GET

- `api/library`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@index`

- `api/library/{id}`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@show`

#### HEAD

- `api/library`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@index`

- `api/library/{id}`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@show`

#### POST

- `api/library`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@store`
  - Roles: admin, faculty

- `api/library/{id}/download`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@download`

- `api/library/{id}/publish`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@publish`
  - Roles: admin, faculty

- `api/library/{id}/unpublish`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@unpublish`
  - Roles: admin, faculty

#### PUT

- `api/library/{id}`
  - Action: `App\Http\Controllers\Api\LibraryResourceController@update`
  - Roles: admin, faculty

### login

#### POST

- `api/login`
  - Action: `App\Http\Controllers\Api\Auth\AuthController@login`

### logout

#### POST

- `api/logout`
  - Action: `App\Http\Controllers\Api\Auth\AuthController@logout`

### majors

#### DELETE

- `api/majors/{id}`
  - Action: `App\Http\Controllers\Api\MajorController@destroy`
  - Roles: admin, faculty

#### GET

- `api/majors`
  - Action: `App\Http\Controllers\Api\MajorController@index`

- `api/majors/{id}`
  - Action: `App\Http\Controllers\Api\MajorController@show`

- `api/majors/{id}/courses`
  - Action: `App\Http\Controllers\Api\MajorController@courses`

- `api/majors/{id}/faculty`
  - Action: `App\Http\Controllers\Api\MajorController@faculty`

#### HEAD

- `api/majors`
  - Action: `App\Http\Controllers\Api\MajorController@index`

- `api/majors/{id}`
  - Action: `App\Http\Controllers\Api\MajorController@show`

- `api/majors/{id}/courses`
  - Action: `App\Http\Controllers\Api\MajorController@courses`

- `api/majors/{id}/faculty`
  - Action: `App\Http\Controllers\Api\MajorController@faculty`

#### POST

- `api/majors`
  - Action: `App\Http\Controllers\Api\MajorController@store`
  - Roles: admin, faculty

#### PUT

- `api/majors/{id}`
  - Action: `App\Http\Controllers\Api\MajorController@update`
  - Roles: admin, faculty

### modules

#### DELETE

- `api/modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@destroy`
  - Roles: admin, faculty

#### GET

- `api/modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@show`

- `api/modules/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseModuleController@assignments`

- `api/modules/{id}/discussions`
  - Action: `App\Http\Controllers\Api\CourseModuleController@discussions`

#### HEAD

- `api/modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@show`

- `api/modules/{id}/assignments`
  - Action: `App\Http\Controllers\Api\CourseModuleController@assignments`

- `api/modules/{id}/discussions`
  - Action: `App\Http\Controllers\Api\CourseModuleController@discussions`

#### PUT

- `api/modules/{id}`
  - Action: `App\Http\Controllers\Api\CourseModuleController@update`
  - Roles: admin, faculty

### notifications

#### DELETE

- `api/notifications/clear-read`
  - Action: `App\Http\Controllers\Api\NotificationController@clearRead`

- `api/notifications/{id}`
  - Action: `App\Http\Controllers\Api\NotificationController@destroy`

#### GET

- `api/notifications`
  - Action: `App\Http\Controllers\Api\NotificationController@index`

- `api/notifications/counts`
  - Action: `App\Http\Controllers\Api\NotificationController@counts`

- `api/notifications/unread`
  - Action: `App\Http\Controllers\Api\NotificationController@unread`

- `api/notifications/urgent`
  - Action: `App\Http\Controllers\Api\NotificationController@urgent`

- `api/notifications/{id}`
  - Action: `App\Http\Controllers\Api\NotificationController@show`

#### HEAD

- `api/notifications`
  - Action: `App\Http\Controllers\Api\NotificationController@index`

- `api/notifications/counts`
  - Action: `App\Http\Controllers\Api\NotificationController@counts`

- `api/notifications/unread`
  - Action: `App\Http\Controllers\Api\NotificationController@unread`

- `api/notifications/urgent`
  - Action: `App\Http\Controllers\Api\NotificationController@urgent`

- `api/notifications/{id}`
  - Action: `App\Http\Controllers\Api\NotificationController@show`

#### PATCH

- `api/notifications/mark-all-read`
  - Action: `App\Http\Controllers\Api\NotificationController@markAllRead`

- `api/notifications/{id}/read`
  - Action: `App\Http\Controllers\Api\NotificationController@markRead`

#### POST

- `api/notifications`
  - Action: `App\Http\Controllers\Api\NotificationController@store`
  - Roles: admin

- `api/notifications/mark-all-read`
  - Action: `App\Http\Controllers\Api\NotificationController@markAllRead`

- `api/notifications/{id}/mark-read`
  - Action: `App\Http\Controllers\Api\NotificationController@markRead`

- `api/notifications/{id}/mark-unread`
  - Action: `App\Http\Controllers\Api\NotificationController@markUnread`

#### PUT

- `api/notifications/mark-all-read`
  - Action: `App\Http\Controllers\Api\NotificationController@markAllRead`

- `api/notifications/{id}`
  - Action: `App\Http\Controllers\Api\NotificationController@update`
  - Roles: admin

- `api/notifications/{id}/read`
  - Action: `App\Http\Controllers\Api\NotificationController@markRead`

### payment

#### GET

- `api/payment/status/{order_id}`
  - Action: `App\Http\Controllers\Api\PaymentController@checkTransactionStatus`

#### HEAD

- `api/payment/status/{order_id}`
  - Action: `App\Http\Controllers\Api\PaymentController@checkTransactionStatus`

#### POST

- `api/payment/create-transaction`
  - Action: `App\Http\Controllers\Api\PaymentController@createTransaction`

- `api/payment/notification`
  - Action: `App\Http\Controllers\Api\PaymentController@notificationHandler`

### products

#### GET

- `api/products`
  - Action: `App\Http\Controllers\Api\ProductController@index`

- `api/products/{id}`
  - Action: `App\Http\Controllers\Api\ProductController@show`

#### HEAD

- `api/products`
  - Action: `App\Http\Controllers\Api\ProductController@index`

- `api/products/{id}`
  - Action: `App\Http\Controllers\Api\ProductController@show`

### public

#### GET

- `api/public/courses`
  - Action: `App\Http\Controllers\Api\CourseController@publicCourses`

#### HEAD

- `api/public/courses`
  - Action: `App\Http\Controllers\Api\CourseController@publicCourses`

### register

#### POST

- `api/register`
  - Action: `App\Http\Controllers\Api\Auth\AuthController@register`

### reset-password

#### POST

- `api/reset-password`
  - Action: `App\Http\Controllers\Api\Auth\AuthController@resetPassword`

### student

#### GET

- `api/student/dashboard`
  - Action: `Closure`
  - Roles: student

- `api/student/my-assignments`
  - Action: `App\Http\Controllers\Api\StudentController@myAssignments`
  - Roles: student

- `api/student/my-courses`
  - Action: `App\Http\Controllers\Api\StudentController@myCourses`
  - Roles: student

- `api/student/my-grades`
  - Action: `App\Http\Controllers\Api\StudentController@myGrades`
  - Roles: student

#### HEAD

- `api/student/dashboard`
  - Action: `Closure`
  - Roles: student

- `api/student/my-assignments`
  - Action: `App\Http\Controllers\Api\StudentController@myAssignments`
  - Roles: student

- `api/student/my-courses`
  - Action: `App\Http\Controllers\Api\StudentController@myCourses`
  - Roles: student

- `api/student/my-grades`
  - Action: `App\Http\Controllers\Api\StudentController@myGrades`
  - Roles: student

### submissions

#### GET

- `api/submissions`
  - Action: `App\Http\Controllers\Api\SubmissionController@index`
  - Roles: student

- `api/submissions/assignment/{assignmentId}`
  - Action: `App\Http\Controllers\Api\SubmissionController@byAssignment`
  - Roles: admin, faculty

- `api/submissions/{id}`
  - Action: `App\Http\Controllers\Api\SubmissionController@show`
  - Roles: student

#### HEAD

- `api/submissions`
  - Action: `App\Http\Controllers\Api\SubmissionController@index`
  - Roles: student

- `api/submissions/assignment/{assignmentId}`
  - Action: `App\Http\Controllers\Api\SubmissionController@byAssignment`
  - Roles: admin, faculty

- `api/submissions/{id}`
  - Action: `App\Http\Controllers\Api\SubmissionController@show`
  - Roles: student

#### POST

- `api/submissions/{id}/feedback`
  - Action: `App\Http\Controllers\Api\SubmissionController@feedback`
  - Roles: admin, faculty

- `api/submissions/{id}/grade`
  - Action: `App\Http\Controllers\Api\SubmissionController@grade`
  - Roles: admin, faculty

#### PUT

- `api/submissions/{id}`
  - Action: `App\Http\Controllers\Api\SubmissionController@update`
  - Roles: student

### user

#### GET

- `api/user`
  - Action: `App\Http\Controllers\Api\UserController@user`

#### HEAD

- `api/user`
  - Action: `App\Http\Controllers\Api\UserController@user`

### users

#### DELETE

- `api/users/{id}`
  - Action: `App\Http\Controllers\Api\UserController@destroy`
  - Roles: admin, faculty

#### GET

- `api/users`
  - Action: `App\Http\Controllers\Api\UserController@index`

- `api/users/faculty/{facultyId}`
  - Action: `App\Http\Controllers\Api\UserController@byFaculty`

- `api/users/list/faculty`
  - Action: `App\Http\Controllers\Api\UserController@faculty`

- `api/users/list/students`
  - Action: `App\Http\Controllers\Api\UserController@students`

- `api/users/major/{majorId}`
  - Action: `App\Http\Controllers\Api\UserController@byMajor`

- `api/users/me/profile`
  - Action: `App\Http\Controllers\Api\UserController@me`

- `api/users/role/{role}`
  - Action: `App\Http\Controllers\Api\UserController@byRole`

- `api/users/{id}`
  - Action: `App\Http\Controllers\Api\UserController@show`

#### HEAD

- `api/users`
  - Action: `App\Http\Controllers\Api\UserController@index`

- `api/users/faculty/{facultyId}`
  - Action: `App\Http\Controllers\Api\UserController@byFaculty`

- `api/users/list/faculty`
  - Action: `App\Http\Controllers\Api\UserController@faculty`

- `api/users/list/students`
  - Action: `App\Http\Controllers\Api\UserController@students`

- `api/users/major/{majorId}`
  - Action: `App\Http\Controllers\Api\UserController@byMajor`

- `api/users/me/profile`
  - Action: `App\Http\Controllers\Api\UserController@me`

- `api/users/role/{role}`
  - Action: `App\Http\Controllers\Api\UserController@byRole`

- `api/users/{id}`
  - Action: `App\Http\Controllers\Api\UserController@show`

#### POST

- `api/users`
  - Action: `App\Http\Controllers\Api\UserController@store`
  - Roles: admin, faculty

- `api/users/me/change-password`
  - Action: `App\Http\Controllers\Api\UserController@changePassword`

- `api/users/{id}/toggle-status`
  - Action: `App\Http\Controllers\Api\UserController@toggleStatus`
  - Roles: admin, faculty

#### PUT

- `api/users/me/profile`
  - Action: `App\Http\Controllers\Api\UserController@updateProfile`

- `api/users/{id}`
  - Action: `App\Http\Controllers\Api\UserController@update`
  - Roles: admin, faculty

