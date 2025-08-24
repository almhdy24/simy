<?php
declare(strict_types=1);

use Simy\Core\Application;
use Simy\Core\Config;

require __DIR__ . "/../vendor/autoload.php";

// Initialize application FIRST
$app = Application::create(dirname(__DIR__));

// Run application
$app->run();
