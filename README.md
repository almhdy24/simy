# Simy Framework

A minimal, lightweight PHP framework for building web applications and APIs.

## Features

- PSR-7 HTTP Message implementation
- Dependency Injection Container
- Flexible routing system
- Middleware support
- Configuration management
- Built-in error handling

## Installation

```bash
git clone https://github.com/almhdy24/simy.git
cd simy
composer install 
## Quick Start

1. Create routes in `routes/web.php`:
```php
$route->get('/', function() {
    return new Simy\Core\Response('Welcome to Simy!');
});
```

2. Run development server:
```bash
php -S localhost:8000 -t public
```

## Documentation

See [DOCS.md](DOCS.md) for complete documentation.