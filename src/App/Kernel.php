<?php
declare(strict_types=1);

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response;
use League\Container\Container;
use League\Route\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Action\User\ListAction;
use App\Repository\UserRepository;
use League\Fractal\Manager as FractalManager;
use Oryx\ORM\EntityManagerFactory;

/**
 * Application Kernel - integrates Oryx MVC and ADR patterns with League Route.
 */
class Kernel
{
    private string $environment;
    private bool $debug;
    private Container $container;
    private Router $router;
    private EventDispatcher $dispatcher;

    public function __construct(string $environment = 'dev', bool $debug = true)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->container = new Container();
        $this->router = new Router();
        $this->dispatcher = new EventDispatcher();
        
        $this->boot();
    }

    public function boot(): void
    {
        $this->registerServices();
        $this->registerRoutes();
    }

    private function registerServices(): void
    {
        $this->container->addShared(FractalManager::class, function () {
            return new FractalManager();
        });

        $this->container->addShared('entity_manager', function () {
            return EntityManagerFactory::createFromEnv();
        });

        $this->container->addShared(UserRepository::class, function ($container) {
            $em = $container->get('entity_manager');
            return new UserRepository($em);
        });

        $this->container->add(ListAction::class, function ($container) {
            return new ListAction(
                $container->get(UserRepository::class),
                $container->get(FractalManager::class)
            );
        });
    }

    private function registerRoutes(): void
    {
        $listAction = $this->container->get(ListAction::class);
        
        $this->router->map('GET', '/users', [$listAction, '__invoke']);
        $this->router->map('GET', '/api/users', [$listAction, '__invoke']);
        $this->router->map('GET', '/health', function (ServerRequestInterface $request): ResponseInterface {
            return new Response\JsonResponse(['status' => 'ok']);
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->router->dispatch($request);
        } catch (\Throwable $e) {
            return new Response\JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $this->debug ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    public function terminate(): void
    {
        // Cleanup if needed
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }
}