<?php
declare(strict_types=1);

namespace Simy\App\Providers;

use Simy\Core\Container;
use Simy\Core\Providers\ServiceProvider;
use Simy\App\Controllers\HomeController;
use Simy\App\Controllers\UserController;
use Simy\App\Controllers\PostController;

class AppServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // Register controllers
        $container->add(HomeController::class, function() {
            return new HomeController();
        });
        
        $container->add(UserController::class, function() {
            return new UserController();
        });
        
        $container->add(PostController::class, function() {
            return new PostController();
        });
        
        // Register services
        $container->addShared('config', function() {
            return [
                'app_name' => 'Simy Framework',
                'version' => '1.0.0',
                'environment' => 'development'
            ];
        });
        
        $container->addShared('logger', function() {
            // Simple logger implementation
            return new class {
                public function log($message) {
                    error_log($message);
                }
            };
        });
    }
    
    public function boot(): void
    {
        // Boot logic here
    }
}