<?php

declare(strict_types=1);

namespace App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use League\Container\Container;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use App\Routing\AdrRoutes;
use App\Middleware\SecurityMiddleware;
use App\Middleware\CorsMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\CsrfMiddleware;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Serializer\JsonApiSerializer;
use Oryx\ORM\EntityManagerFactory;

/**
 * ADR API Kernel - routes separated to App\Routing\AdrRoutes.
 *
 * Middleware Stack (in order):
 * 1. SecurityMiddleware - Headers
 * 2. CorsMiddleware - CORS
 * 3. RateLimitMiddleware - Rate limiting
 * 4. CsrfMiddleware - CSRF protection
 */
class Kernel
{
    private string $environment;
    private bool $debug;
    private Container $container;
    private AdrRoutes $adrRoutes;
    private EntityManager $entityManager;
    private FractalManager $fractal;

    public function __construct(string $environment = 'dev', bool $debug = true)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->container = new Container();

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
    }

    private function registerRoutes(): void
    {
        $this->adrRoutes = new AdrRoutes();
        $router = $this->adrRoutes->getRouter();

        $router->middleware(new SecurityMiddleware());
        $router->middleware(new CorsMiddleware());
        $router->middleware(new RateLimitMiddleware(100, 60));
        $router->middleware(new CsrfMiddleware());
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->adrRoutes->getRouter()->dispatch($request);
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    private function handleError(\Throwable $e): ResponseInterface
    {
        $error = [
            '_error' => [
                'status' => 500,
                'title' => 'Internal Server Error',
                'detail' => $this->debug ? $e->getMessage() : 'An error occurred',
            ],
        ];

        if ($this->debug) {
            $error['_error']['trace'] = $e->getTraceAsString();
        }

        return new JsonResponse($error, 500, [
            'Content-Type' => 'application/hal+json',
        ]);
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
