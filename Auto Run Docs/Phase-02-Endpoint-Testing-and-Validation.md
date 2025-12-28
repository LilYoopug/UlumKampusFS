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
- [ ] Create Auto Run Docs/test-users.sh with curl tests for all user-related endpoints
- [ ] Create Auto Run Docs/test-courses.sh with curl tests for all course-related endpoints
- [ ] Create Auto Run Docs/test-assignments.sh with curl tests for all assignment-related endpoints
- [ ] Create Auto Run Docs/test-grades.sh with curl tests for all grade-related endpoints
- [ ] Create Auto Run Docs/test-announcements.sh with curl tests for all announcement endpoints
- [ ] Create Auto Run Docs/test-discussions.sh with curl tests for all discussion endpoints
- [ ] Create Auto Run Docs/test-calendar.sh with curl tests for all calendar event endpoints
- [ ] Create Auto Run Docs/test-library.sh with curl tests for all library resource endpoints
- [ ] Create Auto Run Docs/test-faculties-majors.sh with curl tests for faculty and major endpoints
- [ ] Create Auto Run Docs/test-notifications.sh with curl tests for all notification endpoints
- [ ] Execute all test scripts and compile results into Auto Run Docs/endpoint-test-results.json with status (success/fail) and error messages
- [ ] Generate Auto Run Docs/phase-02-summary.md with statistics on working vs broken endpoints