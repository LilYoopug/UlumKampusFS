<?php

use Illuminate\Foundation\Application;

// Create the Laravel application
$app = require __DIR__.'/../bootstrap/app.php';

// Laravel 11: The application is already configured and booted
// No need to call Kernel::bootstrap() anymore

return $app;