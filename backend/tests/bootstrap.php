<?php

use Illuminate\Foundation\Application;

// Create the Laravel application
$app = require __DIR__.'/../bootstrap/app.php';

// Laravel 11 test compatibility: the base TestCase expects a Console Kernel
// that can be resolved from the container and bootstrapped.
// The Console Kernel is now registered in routes/console.php for Laravel 11.
//
// Since the bootstrap/app.php doesn't register a Kernel binding,
// we need to bootstrap the application manually here for tests.

// Manually bootstrap the application since Laravel 11 removed automatic bootstrapping
if (!$app->hasBeenBootstrapped()) {
    $app->bootstrapWith([
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);
}

return $app;