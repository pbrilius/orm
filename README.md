# oryx/orm

DQL-centric ORM for PHP 8.2+ with seamless integration to oryx/mvc and oryx/adr architectures.

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

## Architecture Overview

### ADR Pattern (Action-Domain-Responder)

```
Request → Router → Action → Domain/Repository → Responder → Response
```

The ADR pattern separates concerns into three layers:

| Layer | Responsibility | Location |
|-------|---------------|----------|
| **Action** | HTTP handling, input validation | `src/Action/` |
| **Domain** | Business logic, entities | `src/Entity/` |
| **Responder** | Response formatting | `src/Transformer/` |

### MVC Pattern (Model-View-Controller)

```
Request → FrontController → Controller → Model → View → Response
```

## Package Integration

### Symfony Components

| Package | Purpose |
|---------|---------|
| `symfony/console` | CLI commands (`bin/console`) |
| `symfony/dotenv` | Environment variable loading |
| `symfony/event-dispatcher` | Event handling |
| `symfony/string` | String utilities |

### League Packages

| Package | Purpose |
|---------|---------|
| `league/route` | PSR-7/PSR-15 HTTP routing |
| `league/fractal` | JSON:API transformations |
| `league/container` | Dependency injection |
| `league/event` | Event management |
| `league/pipeline` | Pipeline pattern |
| `league/tactician` | Command bus |
| `league/uri` | URI manipulation |

## Console Commands

```bash
# List available commands
bin/console list

# Generate entities from XML schemas
bin/console oryx:entities:generate

# Run Doctrine migrations
bin/console doctrine:migrations:migrate

# Generate migration from schema diff
bin/console doctrine:migrations:diff
```

## RESTful vs AJAX CRUD

### RESTful (Stateless)

Traditional REST API where client manages state.

```php
// RESTful: Full resource representation
GET    /api/users          → ListAction    → 200 + all users
GET    /api/users/{id}     → ShowAction    → 200 + single user
POST   /api/users          → CreateAction  → 201 + created user
PUT    /api/users/{id}     → UpdateAction  → 200 + updated user
DELETE /api/users/{id}     → DeleteAction  → 204 + empty

// Client sends full payload
PUT /api/users/1
{
  "name": "John",
  "email": "john@example.com",
  "groups": [1, 2, 3]
}

// Response includes full resource
{
  "data": {
    "id": 1,
    "type": "users",
    "attributes": {
      "name": "John",
      "email": "john@example.com"
    }
  }
}
```

### AJAX (State-Driven)

Modern SPAs where server manages resource state.

```php
// AJAX: Partial updates, optimistic UI
GET    /api/users          → ListAction    → 200 + users (paginated)
GET    /api/users/{id}     → ShowAction    → 200 + user
PATCH  /api/users/{id}     → UpdateAction  → 200 + updated fields
DELETE /api/users/{id}     → DeleteAction  → 200 + {deleted: true}

// Client sends only changed fields
PATCH /api/users/1
{
  "email": "new@example.com"
}

// Server returns minimal response
{
  "data": {
    "id": 1,
    "email": "new@example.com",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

### Comparison Matrix

| Aspect | RESTful | AJAX/SPAs |
|--------|---------|-----------|
| **State** | Client-managed | Server-managed |
| **Payload** | Full resource | Partial/diff |
| **Caching** | HTTP caching | Client-side cache |
| **Bandwidth** | Higher | Lower |
| **Complexity** | Simple protocol | Complex client |
| **Best for** | Public APIs | Real-time apps |

## League Fractal Usage

### Collection Transformation

```php
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use App\Transformer\Resource\UserTransformer;

$fractal = new Manager();
$fractal->setSerializer(new JsonApiSerializer());

$users = $repository->findAll();
$resource = new Collection($users, new UserTransformer());

$data = $fractal->createData($resource)->toArray();
```

### Single Resource Transformation

```php
use League\Fractal\Resource\Item;

$user = $repository->find($id);
$resource = new Item($user, new UserTransformer());

$data = $fractal->createData($resource)->toArray();
```

## ADR Action Example

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

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $users = $this->repository->findAll();
        
        $resource = new Collection($users, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

## Kernel Integration

```php
// src/App/Kernel.php
namespace App;

use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Doctrine\ORM\EntityManager;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Serializer\JsonApiSerializer;
use Oryx\ORM\EntityManagerFactory;

class Kernel
{
    private Router $router;
    private EntityManager $em;
    private FractalManager $fractal;

    public function __construct()
    {
        $this->em = EntityManagerFactory::createFromEnv();
        $this->fractal = new FractalManager();
        $this->fractal->setSerializer(new JsonApiSerializer());
        $this->router = new Router();
        
        $this->router->setStrategy(new JsonStrategy(new ResponseFactory()));
        $this->registerRoutes();
    }
}
```

## Development

```bash
# Install dependencies
composer install

# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run static analysis
composer stan

# Run tests
composer test
```

## License

MIT
