<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     * Laravel 11 doesn't use Kernel::bootstrap() anymore.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Laravel 11: The application is already configured in bootstrap/app.php
        // No need to call Kernel::bootstrap() anymore

        return $app;
    }

    /**
     * Refresh the application instance.
     * Override to properly set the app instance for Laravel 11.
     */
    protected function refreshApplication(): void
    {
        $this->app = $this->createApplication();
    }
}
