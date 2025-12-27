<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        // Laravel 11 doesn't use Kernel::bootstrap()
        // The application bootstrapping is handled differently
        // in the new structure

        return $app;
    }
}