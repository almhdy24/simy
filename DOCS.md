# Simy Framework Documentation

## Table of Contents
1. [Routing](#routing)
2. [Controllers](#controllers)
3. [Dependency Injection](#dependency-injection)
...

## Routing

### Basic Routes
```php
$route->get('/path', $handler);
$route->post('/path', $handler);
```

### Route Parameters
```php
$route->get('/users/{id}', function($request) {
    $id = $request->getAttribute('id');
});
```

[Full routing documentation...]