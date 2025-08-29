<?php
declare(strict_types=1);

namespace App\Providers;

use Simy\Core\Container;
use Simy\Core\Providers\ServiceProvider;
use App\Controllers\HomeController;

class AppServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // Register controller
        $container->add(HomeController::class, function() {
            return new HomeController();
        });
        
        // Register services
        $container->addShared('config', function() {
            return [
                'app_name' => 'Simy Framework',
                'version' => '1.0.2',
                'environment' => 'development'
            ];
        });
        
        $container->addShared('logger', function() {
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
