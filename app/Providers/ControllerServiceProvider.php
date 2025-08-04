<?php
namespace Simy\App\Providers;

use Simy\App\Controllers\HomeController;
use Simy\App\Controllers\AdminController;
use Simy\Core\Providers\ServiceProvider;

class ControllerServiceProvider extends ServiceProvider
{
    protected array $provides = [
        HomeController::class,
        AdminController::class
    ];

    public function register(): void
    {
        $container = $this->getContainer();
        
        $container->addShared(HomeController::class);
        $container->addShared(AdminController::class);
    }
}