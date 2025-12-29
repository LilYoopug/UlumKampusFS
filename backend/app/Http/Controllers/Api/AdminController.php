<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Faculty;
use App\Models\Major;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends ApiController
{
    /**
     * Get admin dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        return $this->success([
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_faculty' => User::where('role', 'faculty')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_courses' => Course::count(),
            'active_courses' => Course::where('is_active', true)->count(),
            'total_enrollments' => CourseEnrollment::count(),
            'active_enrollments' => CourseEnrollment::where('status', 'enrolled')->count(),
        ]);
    }

    /**
     * Get all users (admin only).
     */
    public function users(): JsonResponse
    {
        $users = User::with(['faculty', 'major'])->get();
        return $this->success($users);
    }

    /**
     * Get comprehensive system overview for super admin.
     */
    public function systemOverview(Request $request): JsonResponse
    {
        $cacheKey = 'admin_system_overview';
        $overview = Cache::remember($cacheKey, 600, function () {
            return [
                'system_info' => [
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'environment' => config('app.env'),
                    'debug_mode' => config('app.debug'),
                    'timezone' => config('app.timezone'),
                    'cache_driver' => config('cache.default'),
                    'session_driver' => config('session.driver'),
                    'queue_connection' => config('queue.default'),
                    'mail_driver' => config('mail.default'),
                    'filesystem_default' => config('filesystems.default'),
                ],
                'database_info' => [
                    'connection' => config('database.default'),
                    'host' => config('database.connections.' . config('database.default') . '.host'),
                    'database' => config('database.connections.' . config('database.default') . '.database'),
                    'migrations_run' => $this->getMigrationCount(),
                    'table_count' => $this->getTableCount(),
                ],
                'storage_info' => [
                    'disk_usage' => $this->getDiskUsage(),
                    'backups_available' => $this->getBackupCount(),
                    'log_files' => $this->getLogFilesInfo(),
                ],
                'security_info' => [
                    'csrf_protection' => config('app.csrf_protection', true),
                    'password_encryption' => 'bcrypt',
                    'session_lifetime' => config('session.lifetime') . ' minutes',
                    'sanctum_enabled' => class_exists('Laravel\Sanctum\SanctumServiceProvider'),
                ],
                'recent_activity' => $this->getRecentAdminActivity(),
            ];
        });

        return $this->success($overview);
    }

    /**
     * Get advanced user management data.
     */
    public function userManagement(Request $request): JsonResponse
    {
        $query = User::with(['faculty', 'major']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by faculty
        if ($request->has('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage);

        return $this->success($users);
    }

    /**
     * Get system configuration management data.
     */
    public function configuration(): JsonResponse
    {
        $config = [
            'app_settings' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'fallback_locale' => config('app.fallback_locale'),
            ],
            'database_settings' => [
                'default_connection' => config('database.default'),
                'connections' => array_keys(config('database.connections')),
                'migration_status' => $this->getMigrationStatus(),
            ],
            'cache_settings' => [
                'default' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
                'prefix' => config('cache.prefix'),
            ],
            'mail_settings' => [
                'default_mailer' => config('mail.default'),
                'mailers' => array_keys(config('mail.mailers')),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'filesystem_settings' => [
                'default' => config('filesystems.default'),
                'cloud_disks' => array_keys(config('filesystems.disks')),
                'links' => config('filesystems.links'),
            ],
            'session_settings' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
                'encrypt' => config('session.encrypt'),
                'path' => config('session.path'),
                'domain' => config('session.domain'),
            ],
            'queue_settings' => [
                'default' => config('queue.default'),
                'connections' => array_keys(config('queue.connections')),
                'failed_driver' => config('queue.failed.driver'),
            ],
        ];

        return $this->success($config);
    }

    /**
     * Perform system maintenance actions.
     */
    public function maintenance(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:clear_cache,clear_config,clear_routes,clear_views,clear_all,optimize,optimize_clear'
        ]);

        try {
            switch ($request->action) {
                case 'clear_cache':
                    \Artisan::call('cache:clear');
                    $message = 'Application cache cleared successfully';
                    break;
                case 'clear_config':
                    \Artisan::call('config:clear');
                    $message = 'Configuration cache cleared successfully';
                    break;
                case 'clear_routes':
                    \Artisan::call('route:clear');
                    $message = 'Route cache cleared successfully';
                    break;
                case 'clear_views':
                    \Artisan::call('view:clear');
                    $message = 'View cache cleared successfully';
                    break;
                case 'clear_all':
                    \Artisan::call('cache:clear');
                    \Artisan::call('config:clear');
                    \Artisan::call('route:clear');
                    \Artisan::call('view:clear');
                    $message = 'All caches cleared successfully';
                    break;
                case 'optimize':
                    \Artisan::call('config:cache');
                    \Artisan::call('route:cache');
                    \Artisan::call('view:cache');
                    $message = 'Application optimized successfully';
                    break;
                case 'optimize_clear':
                    \Artisan::call('config:clear');
                    \Artisan::call('route:clear');
                    \Artisan::call('view:clear');
                    $message = 'Optimization caches cleared successfully';
                    break;
                default:
                    return $this->error('Invalid maintenance action');
            }

            return $this->success(['message' => $message]);
        } catch (\Exception $e) {
            return $this->error('Maintenance action failed: ' . $e->getMessage());
        }
    }

    /**
     * Get migration count.
     */
    private function getMigrationCount(): int
    {
        try {
            return count(\DB::table('migrations')->pluck('migration'));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get table count.
     */
    private function getTableCount(): int
    {
        try {
            return count(\DB::select('SHOW TABLES'));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get disk usage information.
     */
    private function getDiskUsage(): array
    {
        try {
            $total = disk_total_space('/');
            $free = disk_free_space('/');
            $used = $total - $free;
            
            return [
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'used' => $this->formatBytes($used),
                'usage_percentage' => round(($used / $total) * 100, 2)
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to retrieve disk usage'];
        }
    }

    /**
     * Get backup count.
     */
    private function getBackupCount(): int
    {
        try {
            $backupPath = storage_path('app/backups');
            if (!is_dir($backupPath)) {
                return 0;
            }
            return count(glob($backupPath . '/*.zip'));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get log files information.
     */
    private function getLogFilesInfo(): array
    {
        try {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/*.log');
            $logInfo = [];
            
            foreach ($files as $file) {
                $logInfo[] = [
                    'name' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'modified' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
            
            return $logInfo;
        } catch (\Exception $e) {
            return ['error' => 'Unable to retrieve log files information'];
        }
    }

    /**
     * Get recent admin activity.
     */
    private function getRecentAdminActivity(): array
    {
        // This would typically come from an activity log
        return [
            ['action' => 'User registration', 'details' => 'New user registered', 'time' => '2 minutes ago'],
            ['action' => 'Course creation', 'details' => 'New course created', 'time' => '5 minutes ago'],
            ['action' => 'System backup', 'details' => 'Automatic backup completed', 'time' => '1 hour ago'],
            ['action' => 'Cache cleared', 'details' => 'Application cache cleared', 'time' => '2 hours ago'],
        ];
    }

    /**
     * Get migration status.
     */
    private function getMigrationStatus(): array
    {
        try {
            $run = \DB::table('migrations')->pluck('migration')->toArray();
            $pending = [];
            
            // Get all migration files
            $migrationFiles = glob(database_path('migrations/*.php'));
            foreach ($migrationFiles as $file) {
                $migration = basename($file, '.php');
                if (!in_array($migration, $run)) {
                    $pending[] = $migration;
                }
            }
            
            return [
                'run' => count($run),
                'pending' => count($pending),
                'total' => count($migrationFiles),
                'pending_migrations' => $pending
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to check migration status'];
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
