# Phase 02: User Management API

This phase implements full CRUD operations for user management including profile updates, user listing with filtering, and role-based access control. This enables the frontend's User Management Page and Profile components.

## Tasks

- [ ] Create UserRequest validation class for store/update operations
- [ ] Create UserController with index, store, show, update, destroy methods
- [ ] Implement user listing with role and faculty filtering
- [ ] Implement user search by name and email
- [ ] Add user profile update endpoint
- [ ] Create UserResource with all user fields and relationships
- [ ] Add API routes for user management under /api/users
- [ ] Add role-based middleware protection for user CRUD
- [ ] Create UserController tests for all CRUD operations
- [ ] Create feature tests for user filtering and search
- [ ] Test UserResource serialization
- [ ] Verify role-based access control works correctly