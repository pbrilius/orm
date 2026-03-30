# Oryx ORM Web Skeleton - Step-by-Step Tutorial

## Table of Contents
1. [Installation](#1-installation)
2. [Architecture Overview](#2-architecture-overview)
3. [MVC Pattern (Vanilla PHP)](#3-mvc-pattern-vanilla-php)
4. [ADR Pattern (laminas/diactoros)](#4-adr-pattern-laminasdiactoros)
5. [League Fractal Transformers](#5-league-fractal-transformers)
6. [League Factory Muffin Fixtures](#6-league-factory-muffin-fixtures)
7. [PSR Middleware Security](#7-psr-middleware-security)
8. [API RESTfulness CRUD](#8-api-restfulness-crud)
9. [Running the Application](#9-running-the-application)

---

## 1. Installation

```bash
git clone <repository>
cd oryx-orm
composer install
```

**Requirements:**
- PHP 8.2+
- MariaDB/MySQL or SQLite
- Extensions: mbstring, intl, pdo_mysql

**Environment:**
```bash
cp .env.example .env
# Edit .env with your database credentials
```

---

## 2. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      ORYX WEB SKELETON                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    Entry Points                           │    │
│  │  public/index.php → Routes MVC ↔ ADR                     │    │
│  │  public/mvc.php   → MVC only                             │    │
│  │  public/api.php   → ADR only                             │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              │                                   │
│          ┌──────────────────┴──────────────────┐              │
│          ▼                                      ▼              │
│  ┌─────────────────────┐        ┌─────────────────────────┐   │
│  │    MVC Layer        │        │      ADR Layer          │   │
│  │  (Vanilla PHP)     │        │  (laminas/diactoros)    │   │
│  ├─────────────────────┤        ├─────────────────────────┤   │
│  │ • App\Http\Request │        │ • App\Kernel            │   │
│  │ • App\Http\Response│        │ • App\Action\User\*     │   │
│  │ • App\Http\Router │        │ • League\Fractal        │   │
│  │ • PHP Templates   │        │ • JsonHalResponder       │   │
│  └─────────────────────┘        └─────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. MVC Pattern (Vanilla PHP)

**MVC uses NO external HTTP libraries** - pure PHP for maximum compatibility.

### 3.1 HTTP Layer (Vanilla)

```php
// src/Http/Request.php
namespace App\Http;

class Request
{
    public function getMethod(): string { /* $_SERVER['REQUEST_METHOD'] */ }
    public function getPath(): string { /* parse_url() */ }
    public function get(string $key, $default = null) { /* $_GET */ }
    public function post(string $key, $default = null) { /* $_POST */ }
}
```

```php
// src/Http/Response.php
namespace App\Http;

class Response
{
    public function __construct(string $content, int $status = 200, array $headers = []);
    public function send(): void { /* header() + echo */ }
}
```

```php
// src/Http/Router.php
namespace App\Http;

class Router
{
    public function get(string $path, callable $handler): void;
    public function post(string $path, callable $handler): void;
    public function dispatch(Request $request): ?Response;
}
```

### 3.2 MVC Controller

```php
// src/Controller/UserController.php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

class UserController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function index(): array
    {
        return ['users' => $this->repository->findAll()];
    }
}
```

### 3.3 MVC Application

```php
// src/App/MvcApplication.php
namespace App;

use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\View\ViewRenderer;
use Doctrine\ORM\EntityManager;

class MvcApplication
{
    public function __construct(EntityManager $em)
    {
        $this->router = new Router();
        $this->view = new ViewRenderer();
    }

    public function run(): void
    {
        $request = new Request();
        $response = $this->router->dispatch($request);
        $response->send();
    }
}
```

### 3.4 MVC Routes

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/` | Home page |
| `GET` | `/users` | User list |
| `GET` | `/users/create` | Create form |
| `POST` | `/users/create` | Create user |
| `GET` | `/users/{id}` | Show user |

---

## 4. ADR Pattern (laminas/diactoros)

**ADR uses PSR-7/PSR-15 for modern HTTP handling.**

### 4.1 Kernel Setup

```php
// src/App/Kernel.php
namespace App;

use Laminas\Diactoros\ServerRequestFactory;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;

class Kernel
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->router->setStrategy(new JsonStrategy(new ResponseFactory()));
        $this->registerRoutes();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->router->dispatch($request);
    }
}
```

### 4.2 ADR Actions (Invokable)

```php
// src/Action/User/ListAction.php
namespace App\Action\User;

use App\Fixture\FixtureLoader;
use App\Entity\User;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListAction
{
    private FixtureLoader $loader;
    private Manager $fractal;

    public function __construct(FixtureLoader $loader)
    {
        $this->loader = $loader;
        $this->fractal = new Manager();
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $users = $this->loader->makeMany(User::class, 5);
        $resource = new Collection($users, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/hal+json',
        ]);
    }
}
```

### 4.3 JSON:HAL Responder

```php
// src/Responder/JsonHalResponder.php
namespace App\Responder;

use Laminas\Diactoros\Response\JsonResponse;

class JsonHalResponder
{
    public static function resource(string $type, string $id, array $attributes, array $links = []): JsonResponse
    {
        $data = [
            '_links' => [
                'self' => ['href' => "/{$type}/{$id}"],
            ],
            '_embedded' => [],
        ];

        foreach ($links as $rel => $href) {
            $data['_links'][$rel] = ['href' => $href];
        }

        $data[$type] = array_merge(['id' => $id], $attributes);

        return new JsonResponse($data);
    }

    public static function collection(string $type, array $items, array $meta = []): JsonResponse
    {
        $data = [
            '_links' => [
                'self' => ['href' => "/{$type}"],
            ],
            '_embedded' => [
                $type => $items,
            ],
            '_meta' => $meta,
        ];

        return new JsonResponse($data);
    }

    public static function error(string $title, int $status, string $detail = ''): JsonResponse
    {
        return new JsonResponse([
            '_error' => [
                'status' => $status,
                'title' => $title,
                'detail' => $detail,
            ],
        ], $status);
    }

    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }
}
```

---

## 5. League Fractal Transformers

**Transformers convert entities to HAL format.**

### 5.1 User Transformer

```php
// src/Transformer/Resource/UserTransformer.php
namespace App\Transformer\Resource;

use App\Entity\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['posts', 'group'];

    public function transform(User $user): array
    {
        return [
            'id' => $user->getId() ?? 0,
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('c'),
            'updated_at' => $user->getUpdatedAt()?->format('c'),
        ];
    }

    public function includePosts(User $user)
    {
        return $this->collection($user->getPosts(), new PostTransformer());
    }

    public function includeGroup(User $user)
    {
        return $this->item($user->getGroup(), new GroupTransformer());
    }
}
```

### 5.2 Using Transformers in Actions

```php
// In ADR Action
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\JsonApiSerializer;

$fractal = new Manager();
$fractal->setSerializer(new JsonApiSerializer());

$users = $this->repository->findAll();
$resource = new Collection($users, new UserTransformer());
$data = $fractal->createData($resource)->toArray();
```

---

## 6. League Factory Muffin Fixtures

**Fixtures provide test data generation.**

### 6.1 Factory Definition

```php
// tests/factories/user.factories.php
use App\Entity\User;

$fm->define(User::class)->setDefinitions([
    'email' => 'user{++}@example.com',
    'password' => 'password123',
    'roles' => ['ROLE_USER'],
    'createdAt' => fn() => new \DateTimeImmutable(),
])->setCallback(function (User $user) {
    $user->setGroup(null);
});
```

### 6.2 FixtureLoader

```php
// src/Fixture/FixtureLoader.php
namespace App\Fixture;

use League\FactoryMuffin\FactoryMuffin;

class FixtureLoader
{
    private FactoryMuffin $fm;

    public function __construct()
    {
        $this->fm = new FactoryMuffin();
        $this->fm->loadFactories(__DIR__ . '/../../tests/factories');
    }

    public function make(string $class): object
    {
        return $this->fm->seed(1, $class, [], false)[0];
    }

    public function makeMany(string $class, int $count): array
    {
        return $this->fm->seed($count, $class, [], false);
    }
}
```

### 6.3 Using Fixtures in Tests

```php
// tests/Action/UserActionTest.php
public function testListActionReturnsHalJson(): void
{
    $loader = new FixtureLoader();
    $action = new ListAction($loader);
    
    $result = $action($request, $response);
    
    $this->assertEquals(200, $result->getStatusCode());
    $this->assertEquals('application/hal+json', $result->getHeaderLine('Content-Type'));
}
```

---

## 7. PSR Middleware Security

**Middleware provides security, CORS, rate limiting.**

### 7.1 Security Middleware

```php
// src/Middleware/SecurityMiddleware.php
namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class SecurityMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->withHeader('Content-Security-Policy', "default-src 'self'");
    }
}
```

### 7.2 CORS Middleware

```php
// src/Middleware/CorsMiddleware.php
namespace App\Middleware;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse(null, 204, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $handler->handle($request);
        
        return $response->withHeader('Access-Control-Allow-Origin', '*');
    }
}
```

### 7.3 Rate Limiting Middleware

```php
// src/Middleware/RateLimitMiddleware.php
namespace App\Middleware;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $key = 'rate_limit:' . ($request->getHeaderLine('X-Forwarded-For') ?: 'local');
        
        // Implementation with Redis/Memcached would go here
        
        return $handler->handle($request)
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) ($this->maxRequests - 1));
    }
}
```

### 7.4 Applying Middleware to Kernel

```php
// In Kernel
$this->router->middleware(new SecurityMiddleware());
$this->router->middleware(new CorsMiddleware());
$this->router->middleware(new RateLimitMiddleware(100, 60));
```

---

## 8. API RESTfulness CRUD

**Full CRUD operations with HAL+JSON responses.**

### 8.1 API Endpoints

| Method | Endpoint | Action | Response | Description |
|--------|----------|--------|----------|-------------|
| `GET` | `/api/users` | ListAction | `200 + HAL collection` | List all users |
| `GET` | `/api/users/{id}` | ShowAction | `200 + HAL resource` | Get single user |
| `POST` | `/api/users` | CreateAction | `201 + HAL resource` | Create user |
| `PUT` | `/api/users/{id}` | UpdateAction | `200 + HAL resource` | Full update |
| `PATCH` | `/api/users/{id}` | PatchAction | `200 + HAL resource` | Partial update |
| `DELETE` | `/api/users/{id}` | DeleteAction | `204 No Content` | Delete user |

### 8.2 HAL Response Examples

**Single Resource (GET /api/users/1):**
```json
{
  "_links": {
    "self": { "href": "/api/users/1" },
    "collection": { "href": "/api/users" }
  },
  "user": {
    "id": 1,
    "email": "user@example.com",
    "roles": ["ROLE_USER"],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Collection (GET /api/users):**
```json
{
  "_links": {
    "self": { "href": "/api/users" }
  },
  "_embedded": {
    "users": [
      { "id": 1, "email": "user1@example.com", ... },
      { "id": 2, "email": "user2@example.com", ... }
    ]
  },
  "_meta": {
    "total": 2,
    "page": 1,
    "per_page": 20
  }
}
```

**Error (400/404/422/500):**
```json
{
  "_error": {
    "status": 404,
    "title": "Not Found",
    "detail": "User with ID 999 not found"
  }
}
```

### 8.3 Kernel Routes Registration

```php
// src/App/Kernel.php
private function registerRoutes(): void
{
    // Health check
    $this->router->map('GET', '/health', fn() => new JsonResponse(['status' => 'ok']));

    // User CRUD
    $this->router->map('GET', '/api/users', [ListAction::class, '__invoke']);
    $this->router->map('POST', '/api/users', [CreateAction::class, '__invoke']);
    $this->router->map('GET', '/api/users/{id}', [ShowAction::class, '__invoke']);
    $this->router->map('PUT', '/api/users/{id}', [UpdateAction::class, '__invoke']);
    $this->router->map('PATCH', '/api/users/{id}', [PatchAction::class, '__invoke']);
    $this->router->map('DELETE', '/api/users/{id}', [DeleteAction::class, '__invoke']);

    // PWA Manifest
    $this->router->map('GET', '/manifest.json', fn() => new JsonResponse([
        'name' => 'Oryx ORM App',
        'short_name' => 'OryxApp',
        'display' => 'standalone',
        'start_url' => '/',
    ]));
}
```

---

## 9. Running the Application

### 9.1 Development Server

```bash
# Using PHP built-in server
php -S localhost:8080 -t public

# Using Composer script
composer serve
```

### 9.2 Access Points

| URL | Pattern | Entry |
|-----|---------|-------|
| `http://localhost:8080/` | MVC | Vanilla HTML |
| `http://localhost:8080/users` | MVC | Vanilla HTML |
| `http://localhost:8080/api/users` | ADR | HAL+JSON |
| `http://localhost:8080/api/users/1` | ADR | HAL+JSON |
| `http://localhost:8080/manifest.json` | PWA | JSON Manifest |

### 9.3 Testing

```bash
# Run all tests
composer test

# Run specific suite
vendor/bin/phpunit --testsuite Action

# Run with coverage
vendor/bin/phpunit --coverage-text
```

### 9.4 Console Commands

```bash
# List commands
bin/console list

# Generate entities from XML
bin/console oryx:entities:generate

# Doctrine migrations
bin/console doctrine:migrations:migrate
bin/console doctrine:migrations:diff
```

---

## Summary

| Layer | Pattern | HTTP | Templates | Dependencies |
|-------|---------|------|-----------|---------------|
| **MVC** | Controller → Model → View | Vanilla PHP | PHP | Doctrine ORM |
| **ADR** | Action → Domain → Responder | laminas/diactoros | JSON:HAL | League Fractal |
| **PWA** | Service Worker + Manifest | Both | Cache | Offline-first |

**Key Files:**
```
├── public/
│   ├── index.php      # Unified entry (routes MVC ↔ ADR)
│   ├── mvc.php        # MVC only
│   └── api.php        # ADR only
├── src/
│   ├── Http/          # Vanilla MVC HTTP (no dependencies)
│   ├── Controller/    # MVC Controllers
│   ├── View/          # PHP Templates
│   ├── Action/        # ADR Actions
│   ├── Responder/     # JSON:HAL Responders
│   ├── Transformer/   # League Fractal Transformers
│   └── Fixture/       # League Factory Muffin
└── tests/
    ├── Action/        # ADR Action Tests
    ├── Unit/          # Unit Tests
    └── factories/      # Factory Definitions
```

---

*"The best architecture is the one that fits your needs."* — Unknown
