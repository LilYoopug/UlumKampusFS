# Model-Migration Mismatches Fixed

This document details all the model-migration mismatches that were identified and fixed during Phase 02 of the backend fix process.

## 1. Major Model

### Issue
- Primary key was not set to 'code' as defined in the migration
- The migration defined `string('code')->primary()` but the model was using the default 'id' primary key

### Fix
- Added `protected $primaryKey = 'code';`
- Added `public $incrementing = false;`
- Added `protected $keyType = 'string';`

## 2. Course Model

### Issue
- Missing `instructor_avatar_url` field in the fillable array
- The field was added via migration `2025_12_28_000003_add_missing_frontend_fields_to_models.php` but was not in the model's fillable array

### Fix
- Added `'instructor_avatar_url'` to the fillable array

## 3. PaymentMethod Model

### Issue
- Missing proper casts for boolean fields
- The `is_active` field should be cast as boolean

### Fix
- Added `casts()` method with `'is_active' => 'boolean'`

## 4. PaymentItem Model

### Issue
- Missing proper casts for decimal and date fields
- The `amount` field should be cast as decimal with 2 decimal places

### Fix
- Updated from `$casts` property to `casts()` method (Laravel 9+ standard)
- Added `'amount' => 'decimal:2'` cast
- Kept the `'due_date' => 'date'` cast

## 5. PaymentHistory Model

### Issue
- Missing proper casts for decimal field
- The `amount` field should be cast as decimal with 2 decimal places

### Fix
- Updated from `$casts` property to `casts()` method (Laravel 9+ standard)
- Added `'amount' => 'decimal:2'` cast
- Kept the `'payment_date' => 'datetime'` cast

## 6. CourseEnrollment Model

### Issue
- The `final_grade` field cast was correct but worth noting that it properly matches the migration's `decimal('final_grade', 5, 2)`

### Status
- Confirmed correct casting with `'final_grade' => 'decimal:2'`

## 7. Product Model

### Issue
- Missing casts for decimal field
- The `price` field was not cast properly

### Fix
- Added `casts()` method with `'price' => 'decimal:2'` to match the migration's `decimal('price', 8, 2)`

## Verification

All fixes have been verified by running:
- `./vendor/bin/phpstan analyse backend/app/Models/ --memory-limit=2G` - No errors found
- Comparison of all model attributes with their corresponding migration files
- All models now have proper fillable arrays, casts, and relationships matching the database schema