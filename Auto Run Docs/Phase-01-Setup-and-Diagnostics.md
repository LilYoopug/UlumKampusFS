# Phase 01: Setup and Diagnostics

This phase establishes the foundation for backend fixes by installing all dependencies, setting up the Laravel environment, and generating a comprehensive error report using Larastan. This provides the baseline diagnostic needed for systematic fixes and ensures the development environment is ready for the remediation work.

## Tasks

- [x] Run `composer install` to install all PHP dependencies
- [x] Copy `.env.example` to `.env` if it doesn't exist
- [x] Generate application key with `php artisan key:generate`
- [x] Run `php artisan config:clear` and `php artisan cache:clear` to ensure fresh configuration
- [x] Run `php artisan migrate:fresh --seed` to apply all migrations and seeders
- [x] Verify Laravel application boots successfully with `php artisan --version`
- [x] Install and configure Larastan if not already present
- [x] Run Larastan analysis with `vendor/bin/phpstan analyse --memory-limit=2G` to capture all errors
- [x] Save Larastan output to a file at `backend-fix/larastan-report.txt` for reference
- [x] Run `php artisan optimize:clear` to reset all caches
- [x] Verify basic API routes list with `php artisan route:list --json` and save to `backend-fix/routes-list.json`