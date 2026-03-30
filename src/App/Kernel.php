<?php
declare(strict_types=1);

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use League\Container\Container;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use App\Action\User\ListAction;
use App\Action\User\ShowAction;
use App\Action\User\CreateAction;
use App\Action\User\UpdateAction;
use App\Action\User\DeleteAction;
use App\Repository\UserRepository;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Serializer\JsonApiSerializer;
use Oryx\ORM\EntityManagerFactory;

/**
 * Application Kernel - integrates Oryx MVC + ADR patterns.
 * 
 * This kernel provides:
 * - League Route for routing
 * - ADR pattern for Actions/Responders
 * - Doctrine ORM for data persistence
 * - League Fractal for API transformations
 * - PWA-ready JSON responses
 */
class Kernel
{
    private string $environment;
    private bool $debug;
    private Container $container;
    private Router $router;
    private EventDispatcher $dispatcher;
    private EntityManager $entityManager;
    private FractalManager $fractal;

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
        $this->loadEnvironment();
        $this->createServices();
        $this->registerServices();
        $this->registerRoutes();
    }

    private function loadEnvironment(): void
    {
        $dotenv = new Dotenv();
        $dotenv->bootEnv(dirname(__DIR__, 2) . '/.env');
    }

    private function createServices(): void
    {
        $this->entityManager = EntityManagerFactory::createFromEnv();
        $this->fractal = new FractalManager();
        $this->fractal->setSerializer(new JsonApiSerializer());
    }

    private function registerServices(): void
    {
        $this->container->addShared(EntityManager::class, $this->entityManager);
        $this->container->addShared(FractalManager::class, $this->fractal);

        $this->container->addShared(UserRepository::class, function ($container) {
            return new UserRepository($container->get(EntityManager::class));
        });

        $this->container->add(ListAction::class, function ($container) {
            return new ListAction(
                $container->get(UserRepository::class),
                $container->get(FractalManager::class)
            );
        });

        $this->container->add(ShowAction::class, function ($container) {
            return new ShowAction(
                $container->get(UserRepository::class),
                $container->get(FractalManager::class)
            );
        });

        $this->container->add(CreateAction::class, function ($container) {
            return new CreateAction(
                $container->get(UserRepository::class),
                $container->get(FractalManager::class)
            );
        });

        $this->container->add(UpdateAction::class, function ($container) {
            return new UpdateAction(
                $container->get(UserRepository::class),
                $container->get(FractalManager::class)
            );
        });

        $this->container->add(DeleteAction::class, function ($container) {
            return new DeleteAction(
                $container->get(UserRepository::class)
            );
        });
    }

    private function registerRoutes(): void
    {
        $strategy = new JsonStrategy(new ResponseFactory());
        $this->router->setStrategy($strategy);

        $this->router->map('GET', '/health', function (ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                'status' => 'ok',
                'environment' => $this->environment,
                'timestamp' => date('c'),
            ]);
        });

        $this->router->map('GET', '/api/users', [ListAction::class, '__invoke']);
        $this->router->map('POST', '/api/users', [CreateAction::class, '__invoke']);
        $this->router->map('GET', '/api/users/{id}', [ShowAction::class, '__invoke']);
        $this->router->map('PUT', '/api/users/{id}', [UpdateAction::class, '__invoke']);
        $this->router->map('DELETE', '/api/users/{id}', [DeleteAction::class, '__invoke']);

        $this->router->map('GET', '/manifest.json', function (ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                'name' => 'Oryx ORM App',
                'short_name' => 'OryxApp',
                'description' => 'Full-stack ORM application',
                'start_url' => '/',
                'display' => 'standalone',
                'background_color' => '#ffffff',
                'theme_color' => '#4A90E2',
                'icons' => [
                    ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                    ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
                ],
            ]);
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->router->dispatch($request);
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    private function handleError(\Throwable $e): ResponseInterface
    {
        $error = [
            'error' => [
                'type' => 'https://tools.ietf.org/html/rfc7807',
                'title' => get_class($e),
                'detail' => $e->getMessage(),
                'status' => 500,
            ],
        ];

        if ($this->debug) {
            $error['error']['trace'] = $e->getTraceAsString();
        }

        return new JsonResponse($error, 500);
    }

    public function terminate(): void
    {
        if ($this->entityManager->isOpen()) {
            $this->entityManager->close();
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
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
