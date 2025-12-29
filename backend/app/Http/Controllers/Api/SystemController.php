<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemController extends ApiController
{
    /**
     * Get system health status.
     */
    public function health(): JsonResponse
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
            'services' => []
        ];

        // Check database connection
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = [
                'status' => 'online',
                'response_time' => $this->measureResponseTime(function () {
                    DB::select('SELECT 1');
                })
            ];
        } catch (\Exception $e) {
            $health['services']['database'] = [
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'degraded';
        }

        // Check file storage
        try {
            $testFile = 'health_check_' . time() . '.tmp';
            Storage::put($testFile, 'test');
            Storage::delete($testFile);
            $health['services']['storage'] = [
                'status' => 'online',
                'response_time' => $this->measureResponseTime(function () use ($testFile) {
                    Storage::put($testFile, 'test');
                    Storage::delete($testFile);
                })
            ];
        } catch (\Exception $e) {
            $health['services']['storage'] = [
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'degraded';
        }

        // Check cache
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            Cache::forget($testKey);
            $health['services']['cache'] = [
                'status' => 'online',
                'response_time' => $this->measureResponseTime(function () use ($testKey) {
                    Cache::put($testKey, 'test', 60);
                    Cache::forget($testKey);
                })
            ];
        } catch (\Exception $e) {
            $health['services']['cache'] = [
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'degraded';
        }

        // Check mail service (basic configuration check)
        try {
            $mailConfig = config('mail.default');
            $health['services']['mail'] = [
                'status' => 'online',
                'driver' => $mailConfig,
                'configured' => !empty(config('mail.mailers.' . $mailConfig))
            ];
        } catch (\Exception $e) {
            $health['services']['mail'] = [
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'degraded';
        }

        // Check disk space
        try {
            $diskTotal = disk_total_space('/');
            $diskFree = disk_free_space('/');
            $diskUsage = $diskTotal - $diskFree;
            $diskUsagePercent = ($diskUsage / $diskTotal) * 100;

            $health['services']['disk'] = [
                'status' => $diskUsagePercent > 90 ? 'critical' : ($diskUsagePercent > 80 ? 'warning' : 'online'),
                'total' => $this->formatBytes($diskTotal),
                'free' => $this->formatBytes($diskFree),
                'usage' => $this->formatBytes($diskUsage),
                'usage_percent' => round($diskUsagePercent, 2)
            ];

            if ($diskUsagePercent > 90) {
                $health['status'] = 'critical';
            } elseif ($diskUsagePercent > 80 && $health['status'] === 'healthy') {
                $health['status'] = 'degraded';
            }
        } catch (\Exception $e) {
            $health['services']['disk'] = [
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            $health['status'] = 'degraded';
        }

        return $this->success($health);
    }

    /**
     * Get system configuration.
     */
    public function config(): JsonResponse
    {
        $config = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
            ],
            'cache' => [
                'default' => config('cache.default'),
                'driver' => config('cache.stores.' . config('cache.default') . '.driver'),
            ],
            'mail' => [
                'default' => config('mail.default'),
                'driver' => config('mail.mailers.' . config('mail.default') . '.transport'),
            ],
            'filesystems' => [
                'default' => config('filesystems.default'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime'),
            ],
            'queue' => [
                'default' => config('queue.default'),
            ],
        ];

        return $this->success($config);
    }

    /**
     * Get system logs.
     */
    public function logs(): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            return $this->success(['logs' => [], 'message' => 'No log file found']);
        }

        try {
            $logs = File::lastLines($logFile, 100);
            $logEntries = collect(explode("\n", $logs))
                ->filter()
                ->map(function ($line) {
                    // Parse log line to extract timestamp, level, and message
                    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(.*?): (.*)/', $line, $matches)) {
                        return [
                            'timestamp' => $matches[1],
                            'level' => $matches[2],
                            'channel' => $matches[3],
                            'message' => $matches[4],
                            'raw' => $line
                        ];
                    }
                    return ['raw' => $line];
                })
                ->values()
                ->toArray();

            return $this->success(['logs' => $logEntries]);
        } catch (\Exception $e) {
            return $this->error('Failed to read log file: ' . $e->getMessage());
        }
    }

    /**
     * Clear system caches.
     */
    public function clearCache(): JsonResponse
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return $this->success(['message' => 'All caches cleared successfully']);
        } catch (\Exception $e) {
            return $this->error('Failed to clear caches: ' . $e->getMessage());
        }
    }

    /**
     * Get system statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'system' => [
                'uptime' => $this->getSystemUptime(),
                'memory_usage' => $this->getMemoryUsage(),
                'cpu_usage' => $this->getCpuUsage(),
                'disk_usage' => $this->getDiskUsage(),
            ],
            'laravel' => [
                'version' => \Illuminate\Foundation\Application::VERSION,
                'environment' => config('app.env'),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
            ],
            'database' => [
                'connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
                'queries_per_second' => $this->getQueriesPerSecond(),
                'slow_queries' => DB::select('SHOW STATUS LIKE "Slow_queries"')[0]->Value ?? 0,
            ],
        ];

        return $this->success($stats);
    }

    /**
     * Create system backup.
     */
    public function createBackup(): JsonResponse
    {
        try {
            $backupFileName = 'backup_' . date('Y-m-d_H-i-s') . '.zip';
            $backupPath = storage_path('app/backups/');
            
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            // This is a simplified backup - in production, consider using spatie/laravel-backup
            $filesToBackup = [
                'app/',
                'database/',
                'config/',
                'routes/',
                'resources/',
                'storage/app/',
            ];

            $zip = new \ZipArchive();
            if ($zip->open($backupPath . $backupFileName, \ZipArchive::CREATE) === TRUE) {
                foreach ($filesToBackup as $file) {
                    if (File::exists(base_path($file))) {
                        $zip->addGlob(base_path($file) . '*/*', GLOB_BRACE, ['remove_path' => base_path()]);
                    }
                }
                $zip->close();
            }

            return $this->success([
                'message' => 'Backup created successfully',
                'filename' => $backupFileName,
                'size' => $this->formatBytes(File::size($backupPath . $backupFileName))
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Measure response time for a callback.
     */
    private function measureResponseTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        return round(($end - $start) * 1000, 2); // Return in milliseconds
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

    /**
     * Get system uptime.
     */
    private function getSystemUptime(): string
    {
        if (File::exists('/proc/uptime')) {
            $uptime = File::get('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return 'Unknown';
    }

    /**
     * Get memory usage.
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        return [
            'current' => $this->formatBytes($memoryUsage),
            'peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'limit' => $memoryLimit,
            'usage_percent' => $this->calculateMemoryPercent($memoryUsage, $memoryLimit)
        ];
    }

    /**
     * Get CPU usage.
     */
    private function getCpuUsage(): string
    {
        if (File::exists('/proc/loadavg')) {
            $load = File::get('/proc/loadavg');
            $load = explode(' ', $load)[0];
            return $load;
        }
        return 'Unknown';
    }

    /**
     * Get disk usage.
     */
    private function getDiskUsage(): array
    {
        $diskTotal = disk_total_space('/');
        $diskFree = disk_free_space('/');
        $diskUsage = $diskTotal - $diskFree;
        
        return [
            'total' => $this->formatBytes($diskTotal),
            'free' => $this->formatBytes($diskFree),
            'used' => $this->formatBytes($diskUsage),
            'usage_percent' => round(($diskUsage / $diskTotal) * 100, 2)
        ];
    }

    /**
     * Calculate memory usage percentage.
     */
    private function calculateMemoryPercent($usage, $limit): float
    {
        $limitBytes = $this->parseMemoryLimit($limit);
        if ($limitBytes === 0) return 0;
        return round(($usage / $limitBytes) * 100, 2);
    }

    /**
     * Parse memory limit string to bytes.
     */
    private function parseMemoryLimit($limit): int
    {
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (strpos($limit, 'g') !== false) {
            $multiplier = 1024 * 1024 * 1024;
        } elseif (strpos($limit, 'm') !== false) {
            $multiplier = 1024 * 1024;
        } elseif (strpos($limit, 'k') !== false) {
            $multiplier = 1024;
        }
        
        return (int) ((float) $limit * $multiplier);
    }

    /**
     * Get queries per second.
     */
    private function getQueriesPerSecond(): float
    {
        try {
            $status = DB::select('SHOW GLOBAL STATUS LIKE "Questions"')[0]->Value ?? 0;
            $uptime = DB::select('SHOW GLOBAL STATUS LIKE "Uptime"')[0]->Value ?? 1;
            return round($status / $uptime, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
