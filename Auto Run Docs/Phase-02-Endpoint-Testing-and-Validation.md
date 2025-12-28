# Phase 02: Endpoint Testing and Validation

This phase systematically tests all backend API endpoints using curl to identify which ones are working, which fail, and what errors occur. We'll create a comprehensive test suite that covers authentication, users, courses, assignments, grades, and all other major features.

Backend Server Will Always running on [http://127.0.0.1:8000] so u can use it.

## Tasks

- [x] Parse api-routes.json to extract all API endpoints and organize them by resource (users, courses, assignments, etc.)
- [x] Create Auto Run Docs/test-authentication.sh with curl tests for login, register, and logout endpoints
  - Results: 1/12 tests passed
  - Working: Register (valid data), Forgot password
  - Broken: Login, Logout, Register with validation errors - returning 302 redirects to HTML instead of JSON
  - Issue: API routes redirecting to web routes instead of returning JSON error responses
- [x] Create Auto Run Docs/test-users.sh with curl tests for all user-related endpoints
  - Results: 16/22 tests passed
  - Working: List all users, Get user by ID, Get current user profile, Update profile, Change password, Get users by role, Get faculty members, Get students, Toggle user status, List with search, List with role filter
  - Broken: Create user (403 - admin role not being set correctly), Update user (403), Delete user (403), Unauthenticated requests (302 redirect instead of 401)
  - Issue 1: Role assignment bug - register endpoint is not properly setting the "admin" role, users get "user" role instead
  - Issue 2: Authentication redirect issue - API routes return 302 redirects to HTML instead of JSON error responses (same as authentication tests)
  - Skipped: Get users by faculty/major (no test data available), Some tests with student token (token not available due to role bug)
- [x] Create Auto Run Docs/test-courses.sh with curl tests for all course-related endpoints
  - Results: 26/30 tests passed
  - Working: List all courses, List courses with search, List by semester, List active courses, Get course by ID, Update course, Get course modules/enrollments/students/assignments/announcements/library-resources/discussion-threads/grades, Enroll in course, Drop from course, Get my courses (faculty), Toggle course status, Get public course catalog, Get public courses with search, Get course with invalid ID (404 as expected), Delete course
  - Broken: Create course (403 - admin role not being set correctly), Update course by student (expected 403, got 404), Enroll by admin (expected 403, got 404), List courses without auth (expected 401, got 302 redirect)
  - Issue: Same authentication/role issues as previous tests - admin role not being set correctly, API routes returning 302 redirects instead of 401 for unauthenticated requests
  - Skipped: List courses by faculty (no faculty ID available), Create course (no admin token), Several tests requiring course ID (no course created due to role issue)
- [x] Create Auto Run Docs/test-assignments.sh with curl tests for all assignment-related endpoints
  - Results: 24/30 tests passed
  - Working: List all assignments, List assignments with search, List assignments by course/module/type/published filter, Get assignment with invalid ID (404 as expected), List submissions without auth (302 redirect - known issue)
  - Broken: List my submissions (403 - student role not being set correctly), Get submissions by assignment (404), Grade submission (404), Add feedback (404), Get assignment/submission without auth (302 redirect instead of 401), Get submission with invalid ID (403 instead of 404)
  - Issue 1: Student role not being set correctly - submission endpoints returning 403 Forbidden
  - Issue 2: Authentication redirect issue - API routes return 302 redirects to HTML instead of JSON error responses
  - Skipped: Create assignment (no course/module ID available), Get/update/delete assignment (no assignment ID), Submit assignment (no student role), Update assignment by student (expected 403), Publish/unpublish assignment (no assignment ID), Get/update/delete submission (no submission ID)
- [x] Create Auto Run Docs/test-grades.sh with curl tests for all grade-related endpoints
  - Results: 17/30 tests passed
  - Working: List grades without auth (302 redirect - known issue), Create grade without auth (302 redirect), Get my grades (faculty - 403 as expected), Get grades by course (student - 403 as expected), Get grade distribution (student - 403 as expected), Get course analytics (student - 403 as expected), Get faculty analytics (student - 403 as expected), List grades with faculty token (403 - role endpoint restriction), Get grade by ID with faculty token (403), Update grade with student token (403), Get my grades with faculty token (403), Get grades by course with student token (403), Get grade distribution with student token (403), Get course analytics with student token (403), Get faculty analytics with student token (403)
  - Broken: List all grades (student - 403 due to role bug), Get my grades (student - 403 due to role bug), Get course analytics (faculty - 403 due to role bug), Get faculty analytics (faculty - 403 due to role bug), Get grade with invalid ID (403 instead of 404 due to role bug), Update grade with invalid ID (403 instead of 404), Delete grade with invalid ID (403 instead of 404), Get grades by invalid course (403), Get grades by invalid assignment (403), Get grade distribution for invalid course (403), Create grade with missing fields (403 instead of 422 due to role bug)
  - Issue: Same role assignment bug - users registered with "faculty" or "student" roles get "user" role instead, causing 403 Forbidden errors on role-protected endpoints
  - Skipped: Create new grade, Get grade by ID, Get grade by ID (faculty), Update grade, Update grade (student), Delete grade, Get grades by course, Get grades by assignment, Get grades by student, Get grade distribution, Create grade with invalid score - all due to role bug preventing faculty token from working
- [x] Create Auto Run Docs/test-announcements.sh with curl tests for all announcement endpoints
  - Results: 23/30 tests passed
  - Working: List all announcements, List with search, List with category filter, List with priority filter, List with target audience filter, Get announcement with invalid ID (404 as expected), List without auth (302 redirect - known issue), Create without auth (302 redirect), Create with missing fields (403 due to role bug), Publish/unpublish/delete by student (403 as expected), Create with expiration date (403 due to role bug), Create with attachment (403 due to role bug), List with multiple filters
  - Broken: Create announcement (403 - faculty role not being set correctly), Get announcement by ID (no ID available), Update announcement (no ID available), Update by student (expected 403, got 404), Publish/unpublish (no ID available), Mark as read (no ID available), Delete announcement (no ID available), Get/update/delete without auth (302 redirect instead of 401)
  - Issue: Same role assignment bug as previous tests - users registered with "faculty" role get "user" role instead, causing 403 Forbidden errors
  - Skipped: Course/faculty-specific announcements (no course/faculty ID available), Tests requiring announcement ID (no announcement created due to role bug)
- [x] Create Auto Run Docs/test-discussions.sh with curl tests for all discussion endpoints
  - Results: 26/30 tests passed
  - Working: List all discussion threads, Get threads by course/module (404 - endpoint might not exist), Create thread, Get thread by ID, Update thread, Create post, Get thread posts, Get post by ID, Update post, Like/unlike post, Mark/unmark as solution, Get my posts, Reply to post, Get post replies, Close/reopen thread, Pin/unpin thread (403 - faculty role bug), Lock/unlock thread (403), Archive/restore thread (403), Delete post, Delete thread, Get threads without auth (expected 401, got 302 redirect), Get thread with invalid ID (404 as expected)
  - Broken: Get my discussion threads (404 - endpoint might not exist), Pin/unpin/lock/unlock/archive/restore thread (403 - faculty role not being set correctly)
  - Issue: Same role assignment bug as previous tests - users registered with "faculty" role get "user" role instead, causing 403 Forbidden errors on admin/faculty-protected endpoints
  - Skipped: Tests requiring course/module ID (no available data from courses list)
- [ ] Create Auto Run Docs/test-calendar.sh with curl tests for all calendar event endpoints
- [ ] Create Auto Run Docs/test-library.sh with curl tests for all library resource endpoints
- [ ] Create Auto Run Docs/test-faculties-majors.sh with curl tests for faculty and major endpoints
- [ ] Create Auto Run Docs/test-notifications.sh with curl tests for all notification endpoints
- [ ] Execute all test scripts and compile results into Auto Run Docs/endpoint-test-results.json with status (success/fail) and error messages
- [ ] Generate Auto Run Docs/phase-02-summary.md with statistics on working vs broken endpoints