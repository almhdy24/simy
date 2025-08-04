namespace Simy\App\Providers;

use Simy\Core\Container;
use Simy\Core\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $provides = [
        // List all services this provider registers
        'some_service',
        'another_service'
    ];

    public function register(): void
    {
        $container = $this->getContainer();
        
        // Example service registration
        $container->addShared('some_service', function() {
            return new \Some\ServiceClass();
        });
        
        $container->add('another_service', function() {
            return new \Another\ServiceClass();
        });
    }

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }
}