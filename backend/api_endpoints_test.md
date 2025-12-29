# API Endpoints Testing Document

This document lists all API endpoints for the UlumCampus system and their test results.

## Public Endpoints (No Authentication Required)

### Authentication
- `POST /api/register` - Register new user [Status: ✅ Working with proper Accept header]
- `POST /api/login` - User login [Status: ✅ Working after removing recaptcha validation]
- `POST /api/forgot-password` - Forgot password [Status: ✅ Working]
- `POST /api/reset-password` - Reset password [Status: ✅ Working]

### Payment
- `POST /api/payment/notification` - Midtrans payment notification [Status: ❌ Issue - Midtrans Notification class has issue with how it processes request content - SKIPPED FOR NOW]
- `POST /api/payment/create-transaction` - Create payment transaction [Status: ]
- `GET /api/payment/status/{order_id}` - Check payment transaction status [Status: ]

### Public Course Catalog
- `GET /api/public/courses` - Get public course catalog [Status: ✅ Working]

### Health Check
- `GET /api/health` - Health check endpoint [Status: ✅ Working]

## Protected Endpoints (Authentication Required)

### User Profile & Authentication
- `GET /api/user` - Get authenticated user info [Status: ✅ Working]
- `POST /api/logout` - User logout [Status: ✅ Working]

### User Management
- `GET /api/users` - Get all users [Status: ✅ Working]
- `GET /api/users/{id}` - Get user by ID [Status: ✅ Working]
- `GET /api/users/me/profile` - Get current user profile [Status: ✅ Working]
- `PUT /api/users/me/profile` - Update current user profile [Status: ✅ Working]
- `POST /api/users/me/change-password` - Change current user password [Status: ❌ Issue - Incorrect field names expected]
- `GET /api/users/role/{role}` - Get users by role [Status: ❌ Issue - Invalid role specified error]
- `GET /api/users/faculty/{facultyId}` - Get users by faculty [Status: ✅ Working (returns empty array as expected)]
- `GET /api/users/major/{majorId}` - Get users by major [Status: ✅ Working (returns empty array as expected)]
- `GET /api/users/list/faculty` - Get faculty list [Status: ✅ Working (returns empty array as expected)]
- `GET /api/users/list/students` - Get student list [Status: ✅ Working (returns empty array as expected)]

**Admin/Faculty Only:**
- `POST /api/users` - Create user [Status: ]
- `PUT /api/users/{id}` - Update user [Status: ]
- `DELETE /api/users/{id}` - Delete user [Status: ]
- `POST /api/users/{id}/toggle-status` - Toggle user status [Status: ]

### Faculty Management
- `GET /api/faculties` - Get all faculties [Status: ✅ Working]
- `GET /api/faculties/{id}` - Get faculty by ID [Status: ✅ Working]
- `GET /api/faculties/{id}/majors` - Get faculty's majors [Status: ✅ Working (returns empty array as expected)]
- `GET /api/faculties/{id}/courses` - Get faculty's courses [Status: ✅ Working (returns empty array as expected)]

**Admin/Faculty Only:**
- `POST /api/faculties` - Create faculty [Status: ]
- `PUT /api/faculties/{id}` - Update faculty [Status: ]
- `DELETE /api/faculties/{id}` - Delete faculty [Status: ]

### Major Management
- `GET /api/majors` - Get all majors [Status: ✅ Working]
- `GET /api/majors/{code}` - Get major by code [Status: ✅ Working]
- `GET /api/majors/{code}/faculty` - Get major's faculty [Status: ✅ Working]
- `GET /api/majors/{code}/courses` - Get major's courses [Status: ❌ Issue - Database error: no such column: courses.major_code]

**Admin/Faculty Only:**
- `POST /api/majors` - Create major [Status: ]
- `PUT /api/majors/{code}` - Update major [Status: ]
- `DELETE /api/majors/{code}` - Delete major [Status: ]

### Course Management
**Faculty Only:**
- `GET /api/courses/my-courses` - Get current faculty's courses [Status: ]

**Public (Authenticated):**
- `GET /api/courses` - Get all courses [Status: ✅ Working]
- `GET /api/courses/{id}` - Get course by ID [Status: ❌ Issue - No query results for model Course (IDs in list are 0)]
- `GET /api/courses/{id}/modules` - Get course modules [Status: ❌ Issue - No query results for model Course (IDs in list are 0)]
- `GET /api/courses/{id}/enrollments` - Get course enrollments [Status: ]
- `GET /api/courses/{id}/students` - Get course students [Status: ]
- `GET /api/courses/{id}/assignments` - Get course assignments [Status: ]
- `GET /api/courses/{id}/announcements` - Get course announcements [Status: ]
- `GET /api/courses/{id}/library-resources` - Get course library resources [Status: ]
- `GET /api/courses/{id}/discussion-threads` - Get course discussion threads [Status: ]
- `GET /api/courses/{id}/grades` - Get course grades [Status: ]

**Student Only:**
- `POST /api/courses/{id}/enroll` - Enroll in course [Status: ]
- `POST /api/courses/{id}/drop` - Drop from course [Status: ]

**Admin/Faculty Only:**
- `POST /api/courses` - Create course [Status: ]
- `PUT /api/courses/{id}` - Update course [Status: ]
- `DELETE /api/courses/{id}` - Delete course [Status: ]
- `POST /api/courses/{id}/toggle-status` - Toggle course status [Status: ]
- `POST /api/courses/{id}/modules` - Create course module [Status: ]
- `POST /api/courses/{id}/modules/reorder` - Reorder course modules [Status: ]

### Course Module Management
**Public (Authenticated):**
- `GET /api/modules/{id}` - Get module by ID [Status: ]
- `GET /api/modules/{id}/assignments` - Get module assignments [Status: ]
- `GET /api/modules/{id}/discussions` - Get module discussions [Status: ]

**Admin/Faculty Only:**
- `PUT /api/modules/{id}` - Update module [Status: ]
- `DELETE /api/modules/{id}` - Delete module [Status: ]

**Public (Authenticated):**
- `GET /api/course-modules` - Get all course modules [Status: ]
- `GET /api/course-modules/{id}` - Get course module by ID [Status: ]
- `GET /api/course-modules/{id}/assignments` - Get course module assignments [Status: ]
- `GET /api/course-modules/{id}/discussions` - Get course module discussions [Status: ]

**Admin/Faculty Only:**
- `POST /api/course-modules` - Create course module [Status: ]
- `PUT /api/course-modules/{id}` - Update course module [Status: ]
- `DELETE /api/course-modules/{id}` - Delete course module [Status: ]

### Course Enrollment Management
**Student Only:**
- `GET /api/enrollments` - Get student's enrollments [Status: ]
- `GET /api/enrollments/{id}` - Get enrollment by ID [Status: ]

**Admin/Faculty Only:**
- `GET /api/enrollments/course/{courseId}` - Get enrollments by course [Status: ]
- `PUT /api/enrollments/{id}/approve` - Approve enrollment [Status: ]
- `PUT /api/enrollments/{id}/reject` - Reject enrollment [Status: ]
- `DELETE /api/enrollments/{id}` - Delete enrollment [Status: ]

### Assignment Management
**Public (Authenticated):**
- `GET /api/assignments` - Get all assignments [Status: ✅ Working (returns empty array as expected)]
- `GET /api/assignments/{id}` - Get assignment by ID [Status: ]
- `GET /api/assignments/{id}/submissions` - Get assignment submissions [Status: ]

**Student Only:**
- `POST /api/assignments/{id}/submit` - Submit assignment [Status: ]
- `GET /api/assignments/{id}/my-submission` - Get student's submission [Status: ]

**Admin/Faculty Only:**
- `POST /api/assignments` - Create assignment [Status: ]
- `PUT /api/assignments/{id}` - Update assignment [Status: ]
- `DELETE /api/assignments/{id}` - Delete assignment [Status: ]
- `POST /api/assignments/{id}/publish` - Publish assignment [Status: ]
- `POST /api/assignments/{id}/unpublish` - Unpublish assignment [Status: ]

### Assignment Submission Management
**Student Only:**
- `GET /api/submissions` - Get student's submissions [Status: ]
- `GET /api/submissions/{id}` - Get submission by ID [Status: ]
- `PUT /api/submissions/{id}` - Update submission [Status: ]

**Admin/Faculty Only:**
- `GET /api/submissions/assignment/{assignmentId}` - Get submissions by assignment [Status: ]
- `POST /api/submissions/{id}/grade` - Grade submission [Status: ]
- `POST /api/submissions/{id}/feedback` - Give feedback on submission [Status: ]

### Announcement Management
**Public (Authenticated):**
- `GET /api/announcements` - Get all announcements [Status: ✅ Working (returns empty array as expected)]
- `GET /api/announcements/{id}` - Get announcement by ID [Status: ]
- `POST /api/announcements/{id}/mark-read` - Mark announcement as read [Status: ]

**Admin/Faculty Only:**
- `POST /api/announcements` - Create announcement [Status: ]
- `PUT /api/announcements/{id}` - Update announcement [Status: ]
- `DELETE /api/announcements/{id}` - Delete announcement [Status: ]
- `POST /api/announcements/{id}/publish` - Publish announcement [Status: ]
- `POST /api/announcements/{id}/unpublish` - Unpublish announcement [Status: ]

### Library Resource Management
**Public (Authenticated):**
- `GET /api/library` - Get all library resources [Status: ✅ Working (returns empty array as expected)]
- `GET /api/library/{id}` - Get library resource by ID [Status: ]
- `POST /api/library/{id}/download` - Download library resource [Status: ]

**Admin/Faculty Only:**
- `POST /api/library` - Create library resource [Status: ]
- `PUT /api/library/{id}` - Update library resource [Status: ]
- `DELETE /api/library/{id}` - Delete library resource [Status: ]
- `POST /api/library/{id}/publish` - Publish library resource [Status: ]
- `POST /api/library/{id}/unpublish` - Unpublish library resource [Status: ]

**Public (Authenticated):**
- `GET /api/library-resources` - Get all library resources (alias) [Status: ]
- `GET /api/library-resources/{id}` - Get library resource by ID (alias) [Status: ]
- `POST /api/library-resources/{id}/download` - Download library resource (alias) [Status: ]

**Admin/Faculty Only:**
- `POST /api/library-resources` - Create library resource (alias) [Status: ]
- `PUT /api/library-resources/{id}` - Update library resource (alias) [Status: ]
- `DELETE /api/library-resources/{id}` - Delete library resource (alias) [Status: ]
- `POST /api/library-resources/{id}/publish` - Publish library resource (alias) [Status: ]
- `POST /api/library-resources/{id}/unpublish` - Unpublish library resource (alias) [Status: ]

### Discussion Thread Management
**All Authenticated:**
- `GET /api/discussion-threads` - Get all discussion threads [Status: ✅ Working (returns empty array as expected)]
- `GET /api/discussion-threads/{id}` - Get discussion thread by ID [Status: ]
- `GET /api/discussion-threads/{id}/posts` - Get thread posts [Status: ]

**Admin/Faculty Only:**
- `POST /api/discussion-threads` - Create discussion thread [Status: ]

**Thread Owner/Admin/Faculty:**
- `PUT /api/discussion-threads/{id}` - Update discussion thread [Status: ]
- `DELETE /api/discussion-threads/{id}` - Delete discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/close` - Close discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/reopen` - Reopen discussion thread [Status: ]

**Admin/Faculty Only:**
- `POST /api/discussion-threads/{id}/pin` - Pin discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/unpin` - Unpin discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/lock` - Lock discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/unlock` - Unlock discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/archive` - Archive discussion thread [Status: ]
- `POST /api/discussion-threads/{id}/restore` - Restore discussion thread [Status: ]

**User-Specific:**
- `GET /api/discussion-threads/my-threads` - Get user's threads [Status: ]
- `GET /api/discussion-threads/by-course/{courseId}` - Get threads by course [Status: ]
- `GET /api/discussion-threads/by-module/{moduleId}` - Get threads by module [Status: ]

### Legacy Discussion Management
**All Authenticated:**
- `GET /api/discussions` - Get discussions [Status: ]
- `GET /api/discussions/threads` - Get discussion threads [Status: ]
- `GET /api/discussions/threads/{id}` - Get discussion thread [Status: ]
- `GET /api/discussions/threads/{id}/posts` - Get discussion thread posts [Status: ]

**All Authenticated:**
- `POST /api/discussions/threads` - Create discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/posts` - Create discussion post [Status: ]
- `PUT /api/discussions/threads/{id}` - Update discussion thread [Status: ]
- `PUT /api/discussions/posts/{id}` - Update discussion post [Status: ]
- `DELETE /api/discussions/threads/{id}` - Delete discussion thread [Status: ]
- `DELETE /api/discussions/posts/{id}` - Delete discussion post [Status: ]
- `POST /api/discussions/threads/{id}/like` - Like discussion post [Status: ]

**Admin/Faculty Only:**
- `POST /api/discussions/threads/{id}/pin` - Pin discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/unpin` - Unpin discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/lock` - Lock discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/unlock` - Unlock discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/close` - Close discussion thread [Status: ]
- `POST /api/discussions/threads/{id}/reopen` - Reopen discussion thread [Status: ]
- `POST /api/discussions/posts/{id}/mark-solution` - Mark post as solution [Status: ]
- `POST /api/discussions/posts/{id}/unmark-solution` - Unmark post as solution [Status: ]

### Discussion Post Management
**All Authenticated:**
- `POST /api/discussion-threads/{id}/posts` - Create post in thread [Status: ]

**All Authenticated:**
- `GET /api/discussion-posts/{id}` - Get discussion post by ID [Status: ]
- `GET /api/discussion-posts/{id}/replies` - Get post replies [Status: ]

**All Authenticated:**
- `POST /api/discussion-posts/{id}/reply` - Reply to post [Status: ]

**User-Specific:**
- `GET /api/discussion-posts/my-posts` - Get user's posts [Status: ]
- `GET /api/discussion-posts/by-thread/{threadId}` - Get posts by thread [Status: ]
- `GET /api/discussion-posts/solution-by-thread/{threadId}` - Get solution posts by thread [Status: ]

**Post Owner:**
- `PUT /api/discussion-posts/{id}` - Update discussion post [Status: ]
- `DELETE /api/discussion-posts/{id}` - Delete discussion post [Status: ]

**All Authenticated:**
- `POST /api/discussion-posts/{id}/like` - Like post [Status: ]
- `POST /api/discussion-posts/{id}/unlike` - Unlike post [Status: ]

**Thread Creator/Admin:**
- `POST /api/discussion-posts/{id}/mark-as-solution` - Mark post as solution [Status: ]
- `POST /api/discussion-posts/{id}/unmark-as-solution` - Unmark post as solution [Status: ]

### Notification Management
**User Only:**
- `GET /api/notifications` - Get user notifications [Status: ✅ Working]
- `GET /api/notifications/{id}` - Get notification by ID [Status: ]
- `POST /api/notifications/{id}/mark-read` - Mark notification as read [Status: ]
- `POST /api/notifications/{id}/mark-unread` - Mark notification as unread [Status: ]
- `POST /api/notifications/mark-all-read` - Mark all notifications as read [Status: ]
- `GET /api/notifications/unread` - Get unread notifications [Status: ]
- `GET /api/notifications/urgent` - Get urgent notifications [Status: ]
- `GET /api/notifications/counts` - Get notification counts [Status: ]
- `DELETE /api/notifications/{id}` - Delete notification [Status: ]
- `DELETE /api/notifications/clear-read` - Clear read notifications [Status: ]

**Admin Only:**
- `POST /api/notifications` - Create notification [Status: ]
- `PUT /api/notifications/{id}` - Update notification [Status: ]

### Grade Management
**Student Only:**
- `GET /api/grades` - Get student grades [Status: ❌ Issue - Forbidden: You don't have the required role]
- `GET /api/grades/my-grades` - Get student's grades [Status: ❌ Issue - Forbidden: You don't have the required role]
- `GET /api/grades/{id}` - Get grade by ID [Status: ]

**Admin/Faculty Only:**
- `GET /api/grades/course/{courseId}` - Get grades by course [Status: ]
- `GET /api/grades/assignment/{assignmentId}` - Get grades by assignment [Status: ]
- `GET /api/grades/student/{studentId}` - Get grades by student [Status: ]
- `GET /api/grades/distribution/{courseId}` - Get grade distribution [Status: ]
- `GET /api/grades/analytics/faculty` - Get grade analytics by faculty [Status: ]
- `GET /api/grades/analytics/course` - Get grade analytics by course [Status: ]
- `POST /api/grades` - Create grade [Status: ]
- `PUT /api/grades/{id}` - Update grade [Status: ]
- `DELETE /api/grades/{id}` - Delete grade [Status: ]

### Academic Calendar Event Management
**All Authenticated:**
- `GET /api/academic-calendar-events` - Get academic calendar events [Status: ❌ Issue - Call to a member function toIso8601String() on string]
- `GET /api/academic-calendar-events/{id}` - Get academic calendar event by ID [Status: ]

**Admin/Faculty Only:**
- `POST /api/academic-calendar-events` - Create academic calendar event [Status: ]
- `PUT /api/academic-calendar-events/{id}` - Update academic calendar event [Status: ]
- `DELETE /api/academic-calendar-events/{id}` - Delete academic calendar event [Status: ]

**All Authenticated:**
- `GET /api/calendar-events` - Get calendar events (alias) [Status: ]
- `GET /api/calendar-events/{id}` - Get calendar event by ID (alias) [Status: ]

**Admin/Faculty Only:**
- `POST /api/calendar-events` - Create calendar event (alias) [Status: ]
- `PUT /api/calendar-events/{id}` - Update calendar event (alias) [Status: ]
- `DELETE /api/calendar-events/{id}` - Delete calendar event (alias) [Status: ]

### Payment Management
- `POST /api/payment/create-transaction` - Create payment transaction [Status: ]
- `GET /api/payment/status/{order_id}` - Check payment transaction status [Status: ]

### Payment Item Management
- `GET /api/payment-items` - Get all payment items [Status: ✅ Working]
- `GET /api/payment-items/{id}` - Get payment item by ID [Status: ]
- `POST /api/payment-items` - Create payment item [Status: ]
- `PUT /api/payment-items/{id}` - Update payment item [Status: ]
- `DELETE /api/payment-items/{id}` - Delete payment item [Status: ]
- `GET /api/payment-items/user/{userId}` - Get payment items by user [Status: ]
- `GET /api/payment-items/status/{status}` - Get payment items by status [Status: ]

### Payment History Management
- `GET /api/payment-histories` - Get all payment histories [Status: ✅ Working]
- `GET /api/payment-histories/{id}` - Get payment history by ID [Status: ]
- `POST /api/payment-histories` - Create payment history [Status: ]
- `PUT /api/payment-histories/{id}` - Update payment history [Status: ]
- `DELETE /api/payment-histories/{id}` - Delete payment history [Status: ]
- `GET /api/payment-histories/user/{userId}` - Get payment histories by user [Status: ]
- `GET /api/payment-histories/status/{status}` - Get payment histories by status [Status: ]
- `GET /api/payment-histories/method/{paymentMethodId}` - Get payment histories by payment method [Status: ]

### Product Management
- `GET /api/products` - Get all products [Status: ✅ Working (returns empty array as expected)]
- `GET /api/products/{id}` - Get product by ID [Status: ]

### Admin Dashboard
**Admin Only:**
- `GET /api/admin/dashboard` - Get admin dashboard [Status: ]
- `GET /api/admin/stats` - Get admin stats [Status: ]
- `GET /api/admin/users` - Get admin users [Status: ]

### Faculty Dashboard
**Faculty Only:**
- `GET /api/faculty/dashboard` - Get faculty dashboard [Status: ]
- `GET /api/faculty/my-courses` - Get faculty's courses [Status: ]
- `GET /api/faculty/stats` - Get faculty stats [Status: ]

### Student Dashboard
**Student Only:**
- `GET /api/student/dashboard` - Get student dashboard [Status: ]
- `GET /api/student/my-courses` - Get student's courses [Status: ]
- `GET /api/student/my-assignments` - Get student's assignments [Status: ]
- `GET /api/student/my-grades` - Get student's grades [Status: ]

### Dashboard Analytics
**All Authenticated:**
- `GET /api/dashboard` - Get dashboard stats [Status: ❌ Issue - Invalid user role]

**Role-Specific:**
- `GET /api/dashboard/student` - Get student stats [Status: ❌ Issue - Forbidden: You don't have the required role]
- `GET /api/dashboard/faculty` - Get faculty stats [Status: ]
- `GET /api/dashboard/prodi` - Get prodi stats [Status: ]
- `GET /api/dashboard/management` - Get management stats [Status: ]

**Analytics:**
- `GET /api/dashboard/grade-distribution` - Get grade distribution [Status: ]
- `GET /api/dashboard/enrollment-trends` - Get enrollment trends [Status: ]
- `GET /api/dashboard/dosen/{instructorName}` - Get dosen stats [Status: ]
- `GET /api/dashboard/faculty-enrollment` - Get faculty enrollment [Status: ]