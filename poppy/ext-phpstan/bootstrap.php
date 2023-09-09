<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;

define('LARAVEL_START', microtime(true));


if (file_exists($applicationPath = getcwd() . '/storage/bootstrap/app.php')) { // Applications and Local Dev
    $app = require $applicationPath;
}
elseif (file_exists($applicationPath = dirname(__DIR__, 3) . '/storage/bootstrap/app.php')) { // Relative path from default vendor dir
    $app = require $applicationPath;
}
else {
    throw new Exception('Could not find Laravel bootstrap file nor Testbench is installed. Install orchestra/testbench if analyzing a package.');
}


$app->make(Kernel::class)->bootstrap();

define('LARAVEL_VERSION', $app->version());
