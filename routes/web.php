<?php
use Simy\Core\Application;
use Simy\Core\Response;
use Simy\Core\Routing\RouteRegistrar;
use Simy\App\Controllers\HomeController;

/** @var RouteRegistrar $route */
$route = Application::getInstance()
  ->getContainer()
  ->get(RouteRegistrar::class);

// Basic routes
$route->get("/", [HomeController::class, "index"])->name("home");
$route->get("/about", [HomeController::class, "about"])->name("about");
$route->get("/contact", [HomeController::class, "contact"])->name("contact");

// Simple parameter example
$route->get(
  "/user/{id}",
  fn($req) => new Response("User ID: " . $req->getAttribute("id"))
);

// Fallback route
$route
  ->get("/{any}", fn($req) => new Response("Page not found", 404))
  ->name("fallback");
