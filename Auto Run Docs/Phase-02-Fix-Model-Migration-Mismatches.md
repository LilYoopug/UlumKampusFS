# Phase 02: Fix Model-Migration Mismatches

This phase systematically fixes all model definitions to properly match their corresponding database migrations. Models must have correct fillable arrays, casts, primary keys, and relationships based on the actual database schema defined in the migrations. This is the foundational layer that ensures all other backend components can interact with the database correctly.

## Tasks

- [x] Read all migration files in `database/migrations/` directory to build complete schema understanding
- [x] List all model files in `app/Models/` directory
- [x] Compare Faculty model with `2025_12_27_000001_create_faculties_table.php` migration and verify fillable array (confirmed `code` field is included)
- [x] Compare Faculty model with migration and verify all fields, casts, and relationships
- [x] Compare Major model with its migration and verify primary key setting (correctly set to `code`)
- [x] Compare Major model with migration and verify fillable array and casts
- [x] Compare Course model with its migration and identify discrepancies in `mode`, `status`, `image_url` fields (found these were added via add_missing_frontend_fields_to_models migration)
- [x] Fix Course model fillable array to match actual migration columns (already correct)
- [x] Compare User model with its migration and verify all fields, casts, and relationships
- [x] Compare Assignment model with its migration and verify fields
- [x] Compare AssignmentSubmission model with its migration and verify fields
- [x] Compare Announcement model with its migration and verify fields
- [x] Compare PaymentItem model with its migration and verify fields
- [x] Compare PaymentItem model with its migration and verify fields
- [x] Compare PaymentHistory model with its migration and verify fields
- [x] Compare any remaining models with their corresponding migrations
- [x] Add any missing `$fillable` arrays for models that don't have them (none found)
- [x] Add any missing `$casts` for datetime and JSON fields (all properly configured)
- [x] Set correct `$primaryKey` for models using non-standard primary keys (Major model already correctly set to 'code')
- [x] Verify all `$table` properties match migration table names (all using correct default names)
- [x] Run Larastan analysis on models only: `vendor/bin/phpstan analyse app/Models/ --memory-limit=2G` (No errors found)
- [x] Document all fixed model-migration mismatches in `backend-fix/model-fixes.md` (documented in existing file)