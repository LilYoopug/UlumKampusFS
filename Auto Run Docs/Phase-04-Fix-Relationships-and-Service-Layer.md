# Phase 04: Fix Relationships and Service Layer

This phase ensures all model relationships are properly defined and any service classes or additional backend components are correctly implemented. Relationships between models must match the foreign keys and table structures defined in migrations, and service layer must properly use models.

## Tasks

- [x] Review all models for relationship methods (hasOne, hasMany, belongsTo, belongsToMany)
- [x] Verify Faculty hasMany Major relationship exists and uses correct foreign key
- [x] Verify Major belongsTo Faculty relationship exists and uses correct foreign key
- [x] Verify Major hasMany Course relationship if applicable
- [x] Verify Course belongsTo Major relationship if applicable
- [x] Verify User hasMany AssignmentSubmissions relationship if applicable
- [x] Verify Assignment belongsTo Course and hasMany Submissions relationships
- [x] Verify Payment relationships to User, PaymentItems, and PaymentHistory
- [x] Check for any models missing required relationship definitions
- [x] Verify relationship method return types are correctly typed
- [x] List all service classes in `app/Services/` directory if it exists
- [x] Read and review all service classes for correct model usage
- [x] Fix any service classes using non-existent model properties
- [x] List any request classes in `app/Http/Requests/` directory
- [x] Verify all form request validation rules match model migration fields
- [x] List any resource classes in `app/Http/Resources/` directory
- [x] Verify API resources include correct model fields in transformations
- [x] Run Larastan analysis on services and requests: `vendor/bin/phpstan analyse app/Services/ app/Http/Requests/ app/Http/Resources/ --memory-limit=2G`
- [x] Document all relationship and service fixes in `backend-fix/relationship-fixes.md`