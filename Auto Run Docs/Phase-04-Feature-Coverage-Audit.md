# Phase 04: Feature Coverage Audit

This phase compares frontend features and API calls with backend endpoints to identify missing functionality. We'll analyze what the frontend expects versus what the backend provides to find gaps in the API.

Backend Server Will Always running on [http://127.0.0.1:8000] so u can use it.

## Tasks

- [ ] Read frontend/src/services/maestroApiService.ts to extract all API calls made by the frontend
- [ ] Parse endpoint-test-results.json to identify all available backend endpoints
- [ ] Create Auto Run Docs/frontend-api-calls.json listing all frontend API requirements with methods and paths
- [ ] Compare frontend API calls against available backend endpoints
- [ ] Create Auto Run Docs/missing-endpoints.json listing:
  - Endpoints called by frontend but missing from backend
  - Endpoints that exist but return different data structures
  - Endpoints that need additional parameters or headers
- [ ] Analyze role-based access requirements (Mahasiswa, Dosen, Prodi Admin, etc.) and document missing authorization checks
- [ ] Create Auto Run Docs/phase-04-summary.md with comprehensive feature coverage report and prioritized list of missing endpoints