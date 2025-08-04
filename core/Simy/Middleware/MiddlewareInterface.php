<?php
namespace Simy\Core\Middleware;

use Simy\Core\Request;
use Simy\Core\Response;

interface MiddlewareInterface {
public function handle(Request $request, callable $next): Response;
}