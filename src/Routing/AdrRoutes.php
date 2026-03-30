<?php

declare(strict_types=1);

namespace App\Routing;

use App\Action\User\ListAction;
use App\Action\User\ShowAction;
use App\Action\User\CreateAction;
use App\Action\User\UpdateAction;
use App\Action\User\PatchAction;
use App\Action\User\DeleteAction;
use App\Fixture\FixtureLoader;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdrRoutes
{
    private Router $router;
    private FixtureLoader $loader;

    public function __construct()
    {
        $this->loader = new FixtureLoader();
        $this->router = new Router();
        $this->router->setStrategy(new JsonStrategy(new ResponseFactory()));
        $this->register();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    private function register(): void
    {
        $this->router->map('GET', '/health', function (ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                '_links' => [
                    'self' => ['href' => '/health'],
                ],
                'status' => 'ok',
                'timestamp' => date('c'),
            ]);
        });

        $this->router->map('GET', '/api/users', [ListAction::class, '__invoke']);
        $this->router->map('POST', '/api/users', [CreateAction::class, '__invoke']);
        $this->router->map('GET', '/api/users/{id}', [ShowAction::class, '__invoke']);
        $this->router->map('PUT', '/api/users/{id}', [UpdateAction::class, '__invoke']);
        $this->router->map('PATCH', '/api/users/{id}', [PatchAction::class, '__invoke']);
        $this->router->map('DELETE', '/api/users/{id}', [DeleteAction::class, '__invoke']);

        $this->router->map('GET', '/manifest.json', function (ServerRequestInterface $request): ResponseInterface {
            return new JsonResponse([
                'name' => 'Oryx ORM App',
                'short_name' => 'OryxApp',
                'description' => 'Full-stack ORM with ADR pattern',
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
}
