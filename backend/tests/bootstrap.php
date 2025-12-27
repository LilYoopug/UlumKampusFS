<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;

// Create the Laravel application using Laravel 11's configuration
$app = require __DIR__.'/../bootstrap/app.php';

// In Laravel 11, the application is already configured and bootstrapped
// Just ensure the facade root is set
Facade::setFacadeApplication($app);

return $app;