# oryx/orm Web Skeleton

DQL-centric ORM for PHP 8.2+ with seamless integration to **oryx/mvc** and **oryx/adr** architectures.

## Features

- Doctrine DBAL/ORM abstraction with enhanced DQL capabilities
- PSR-4 autoloading compliant
- League package integration (Fractal, Route, Container, Event, Pipeline, Tactician)
- Symfony Dotenv for environment configuration
- Full PSR-12 code style compliance
- Comprehensive test suite with PHPUnit, Mockery & PHPStan
- ADR (Action-Domain-Responder) pattern for API endpoints
- MVC (Model-View-Controller) pattern for web requests
- PWA-ready JSON:API responses via League Fractal

## Requirements

- PHP ^8.2
- Extensions: mbstring, intl, pdo_mysql
- MariaDB/MySQL on LAMP stack

## Installation

```bash
composer install
```

## Package Integration

### Symfony Components

| Package | Usage |
|---------|-------|
| `symfony/console` | `bin/console` CLI commands |
| `symfony/dotenv` | `$dotenv->bootEnv('.env')` |
| `symfony/event-dispatcher` | `$dispatcher->dispatch(new Event())` |

### League Packages

| Package | Usage |
|---------|-------|
| `league/route` | `$router->map('GET', '/path', Handler::class)` |
| `league/fractal` | `$fractal->createData($resource)->toArray()` |
| `league/container` | `$container->get(Service::class)` |
| `league/event` | `EventEmitter` for domain events |
| `league/pipeline` | `PipelineBuilder` for processing |
| `league/tactician` | Command bus pattern |

### Oryx Packages

| Package | Usage |
|---------|-------|
| `oryx/mvc` | MVC dispatcher: Controller → Model → View |
| `oryx/adr` | ADR dispatcher: Action → Domain → Responder |
| `oryx/orm` | EntityManager factory for Doctrine ORM |

## Architecture Patterns

### ADR Pattern (Action-Domain-Responder)

```
Request → Router → Action → Domain/Repository → Fractal → JSON Response
```

### MVC Pattern (Model-View-Controller)

```
Request → FrontController → Controller → Model → View → Response
```

## League/Fractal Usage Examples

### 1. Transformer Definition

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
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('c'),
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

### 2. Collection Transformation

```php
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use App\Transformer\Resource\UserTransformer;

$fractal = new Manager();
$fractal->setSerializer(new JsonApiSerializer());

$users = $userRepository->findAll();
$resource = new Collection($users, new UserTransformer());

$data = $fractal->createData($resource)->toArray();
// → {"data": [{"id": 1, "type": "users", "attributes": {...}}, ...]}
```

### 3. Single Resource with Includes

```php
use League\Fractal\Resource\Item;

$user = $userRepository->find($id);
$resource = new Item($user, new UserTransformer());

$data = $fractal->createData($resource)->toArray();
// → {"data": {...}, "included": {"posts": [...], "group": {...}}}
```

## League/Route Usage

```php
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;

$router = new Router();
$router->setStrategy(new JsonStrategy(new ResponseFactory()));

// Map routes
$router->map('GET', '/api/users', [ListAction::class, '__invoke']);
$router->map('GET', '/api/users/{id}', [ShowAction::class, '__invoke']);
$router->map('POST', '/api/users', [CreateAction::class, '__invoke']);
$router->map('PUT', '/api/users/{id}', [UpdateAction::class, '__invoke']);
$router->map('DELETE', '/api/users/{id}', [DeleteAction::class, '__invoke']);

// Dispatch request
$response = $router->dispatch($request);
```

## Oryx/MVC + Oryx/ADR Integration

### Kernel Setup

```php
// src/App/Kernel.php
namespace App;

use League\Container\Container;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\EntityManager;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Serializer\JsonApiSerializer;
use Oryx\ORM\EntityManagerFactory;

class Kernel
{
    private Container $container;
    private Router $router;
    private EntityManager $em;
    private FractalManager $fractal;

    public function __construct(string $environment = 'dev', bool $debug = true)
    {
        $this->container = new Container();
        $this->router = new Router();
        $this->dispatcher = new EventDispatcher();
        $this->boot();
    }

    private function boot(): void
    {
        (new Dotenv())->bootEnv(dirname(__DIR__, 2) . '/.env');
        
        $this->em = EntityManagerFactory::createFromEnv();
        $this->fractal = new FractalManager();
        $this->fractal->setSerializer(new JsonApiSerializer());
        
        $this->router->setStrategy(new JsonStrategy(new ResponseFactory()));
        $this->registerServices();
        $this->registerRoutes();
    }
}
```

### ADR Action Example

```php
// src/Action/User/ListAction.php
namespace App\Action\User;

use App\Repository\UserRepository;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListAction
{
    public function __construct(
        private UserRepository $repository,
        private Manager $fractal
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $users = $this->repository->findAll();
        
        $resource = new Collection($users, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

## RESTful vs AJAX CRUD

### RESTful (Stateless)

```php
// RESTful: Full resource representation
GET    /api/users          → ListAction    → 200 + all users
GET    /api/users/{id}     → ShowAction    → 200 + single user
POST   /api/users          → CreateAction  → 201 + created user
PUT    /api/users/{id}     → UpdateAction  → 200 + updated user
DELETE /api/users/{id}     → DeleteAction  → 204 + empty
```

### AJAX/SPAs (State-Driven)

```php
// AJAX: Partial updates, optimistic UI
PATCH  /api/users/{id}     → UpdateAction  → 200 + changed fields only
```

### Comparison

| Aspect | RESTful | AJAX/SPAs |
|--------|---------|-----------|
| **State** | Client-managed | Server-managed |
| **Payload** | Full resource | Partial/diff |
| **Caching** | HTTP caching | Client-side cache |
| **Bandwidth** | Higher | Lower |

## Console Commands

```bash
# List available commands
bin/console list

# Generate entities from XML schemas
bin/console oryx:entities:generate

# Run Doctrine migrations
bin/console doctrine:migrations:migrate
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer stan
```

## License

MIT
