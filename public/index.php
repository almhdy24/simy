<?php
declare(strict_types=1);

use Simy\Core\Application;
use Simy\Core\Config;

require __DIR__ . '/../vendor/autoload.php';

// Initialize application FIRST
$app = Application::create(dirname(__DIR__));

// Load configuration
Config::loadFromArray(require __DIR__.'/../app/config/app.php');

// Now load routes (after app is initialized)
require __DIR__.'/../routes/web.php';

// Run application
$app->run();