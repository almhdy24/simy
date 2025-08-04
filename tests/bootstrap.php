<?php
declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

// Set up test environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '8080';