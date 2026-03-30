# Oryx ORM Web Skeleton

## 🧙‍♂️ The Oryx Cathedral: A PHP Apprentice's Guide

Welcome, young wizard! In this enchanted chronicle, you shall learn the ancient arts of **Oryx ORM**, **MVC**, and **ADR** patterns. Like Hogwarts houses, each architectural pattern has its own special purpose.

---

## 🏰 The Three Pillars of Oryx Cathedral

```
┌─────────────────────────────────────────────────────────────────┐
│                    ORYX CATHEDRAL                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────────────┐  │
│   │  oryx/mvc  │    │  oryx/adr   │    │     oryx/orm       │  │
│   ├─────────────┤    ├─────────────┤    ├─────────────────────┤  │
│   │ • Controller│    │ • Action    │    │ • Entity Manager    │  │
│   │ • Model    │    │ • Domain    │    │ • Query Builder     │  │
│   │ • View     │    │ • Responder │    │ • Unit of Work      │  │
│   │ (Plates)   │    │ (JSON:API)  │    │ • XML Schemas       │  │
│   └──────┬──────┘    └──────┬──────┘    └──────────┬──────────┘  │
│          │                  │                       │             │
│          └──────────────────┴───────────────────────┘             │
│                              │                                    │
│                    ┌─────────▼─────────┐                         │
│                    │  League Packages  │                         │
│                    │  • Route         │                         │
│                    │  • Fractal      │                         │
│                    │  • Container    │                         │
│                    │  • Event        │                         │
│                    └─────────────────┘                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🏠 The Oryx/MVC House

Like Gryffindor, **MVC** is the classic house of web development.

```
╔═══════════════════════════════════════════════════════════════╗
║                     MVC FLOW CHART                            ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║   HTTP Request                                                ║
║       │                                                       ║
║       ▼                                                       ║
║   ┌──────────────┐    ┌─────────────┐    ┌──────────────┐  ║
║   │  Controller  │───▶│    Model    │───▶│     View     │  ║
║   │  (Harry)     │    │  (Hermione) │    │   (Ron)      │  ║
║   └──────────────┘    └─────────────┘    └──────────────┘  ║
║         │                  │                   │              ║
║         ▼                  ▼                   ▼              ║
║   HTTP Response ←─────────────── HTML/Templates ───────────  ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

### MVC Package Structure

```php
// vendor/oryx/mvc/src/
Prototype\Mvc\
├── Application.php           // The Enchanted Router
├── AbstractController.php    // Base spell-casting class
├── ControllerInterface.php   // Contract for Controllers
├── Controller/
│   └── HomeController.php    // Specific spell handlers
├── Model/
│   ├── AbstractModel.php     // Data manipulation magic
│   └── ModelInterface.php    // Model contract
└── View/
    ├── PlatesView.php        // Template rendering (League Plates)
    └── ViewInterface.php     // View contract
```

### MVC Code Example

```php
namespace Prototype\Mvc\Controller;

use Prototype\Mvc\AbstractController;

class UserController extends AbstractController
{
    public function index(): void
    {
        $users = $this->model->findAll();
        $this->render('users/index', ['users' => $users]);
    }

    public function show(int $id): void
    {
        $user = $this->model->findById($id);
        $this->render('users/show', ['user' => $user]);
    }
}
```

---

## ⚡ The Oryx/ADR House

Like Slytherin, **ADR** is the modern house for API sorcerers.

```
╔═══════════════════════════════════════════════════════════════╗
║                     ADR FLOW CHART                            ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║   HTTP Request                                                ║
║       │                                                       ║
║       ▼                                                       ║
║   ┌──────────┐     ┌─────────────┐     ┌────────────────┐    ║
║   │  Action   │────▶│   Domain    │────▶│   Responder   │    ║
║   │ (Hermione)│     │ (Textbook)  │     │ (Magic Quill) │    ║
║   └──────────┘     └─────────────┘     └────────────────┘    ║
║         │                  │                    │             ║
║         ▼                  ▼                    ▼             ║
║   Validation         Business Logic      JSON:API Output     ║
║                                                               ║
║   Response ◀────────────────────────────────────────────────  ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

### ADR Package Structure

```php
// vendor/oryx/adr/src/
Oryx\Adr\
├── Action/
│   ├── ActionInterface.php      // Action contract
│   └── AbstractAction.php       // Base action spell
├── Domain/
│   ├── DomainInterface.php      // Domain contract
│   └── AbstractDomain.php       // Business logic base
├── Responder/
│   ├── ResponderInterface.php   // Responder contract
│   ├── JsonApiResponder.php     // JSON:API formatter ✨
│   ├── ProblemDetailsResponder.php // Error handler
│   ├── DefaultResponderFactory.php
│   └── ResponderFactory.php     // Responder creator
└── Middleware/
    └── ManifestJsonMiddleware.php // PWA manifest
```

### ADR Code Example

```php
// src/Action/User/ListAction.php
namespace App\Action\User;

use App\Domain\UserDomain;
use Oryx\Adr\Action\AbstractAction;
use Oryx\Adr\Responder\JsonApiResponder;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class ListAction extends AbstractAction
{
    public function __construct(
        UserDomain $domain,
        ResponderFactory $factory,
        private Manager $fractal
    ) {
        parent::__construct($domain, $factory);
    }

    public function execute(DomainInterface $domain): string
    {
        $users = $domain->findAll();
        
        $resource = new Collection($users, new UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();
        
        return JsonApiResponder::resourceCollection('users', $data);
    }
}
```

### JsonApiResponder Magic

```php
// Single resource
return JsonApiResponder::singleResource(
    'users',
    $user->getId(),
    ['email' => $user->getEmail(), 'name' => $user->getName()],
    ['group' => ['type' => 'groups', 'id' => '1']]
);

// Collection
return JsonApiResponder::resourceCollection('users', $usersArray, [
    'self' => '/api/users',
    'next' => '/api/users?page=2'
]);

// No content (DELETE)
return JsonApiResponder::noContent();
```

---

## 📦 The Package Ecosystem

### Symfony Components

| Component | Magical Property | Example |
|-----------|-----------------|---------|
| `symfony/console` | CLI wand | `bin/console` |
| `symfony/dotenv` | Env owl | `(new Dotenv())->bootEnv('.env')` |
| `symfony/event-dispatcher` | Message patronus | `$dispatcher->dispatch($event)` |
| `symfony/string` | String enchantment | `Uuid::generate()` |

### League Packages

| Package | Magical Property | Example |
|---------|-----------------|---------|
| `league/route` | Routing Floo Network | `$router->map('GET', '/path', Handler)` |
| `league/fractal` | JSON:API metamorphosis | `$fractal->createData($resource)` |
| `league/container` | Potion container | `$container->get(Service::class)` |
| `league/plates` | Template spellbook | `$engine->render('template', $data)` |
| `league/event` | Event charms | `Emitter::emit('event', $payload)` |
| `league/pipeline` | Processing pipeweed | `$pipeline->send($data)->then($processor)` |
| `league/tactician` | Command bus | `$bus->handle($command)` |

---

## 🎓 The Oryx ORM Grimoire

### Entity Generation from XML

```bash
# Generate entities from XML schemas
bin/console oryx:entities:generate
```

### XML Schema Example

```xml
<!-- src/Schema/definitions/User.orm.xml -->
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="https://doctrine-project.org/schemas/orm/doctrine-mapping">

    <entity name="App\Entity\User" table="users">
        <id name="id" type="integer">
            <generator strategy="IDENTITY"/>
        </id>
        <field name="email" type="string" unique="true"/>
        <field name="roles" type="json"/>
        <field name="password" type="string"/>
        <field name="createdAt" type="datetime"/>
        <field name="updatedAt" type="datetime" nullable="true"/>
        
        <one-to-many target-entity="Post" field="posts" mapped-by="user"/>
        <many-to-one target-entity="Group" field="group"/>
    </entity>
</doctrine-mapping>
```

### League/Fractal Transformers

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

### League/Factory-Muffin Fixtures

Create test fixtures easily using Factory Muffin:

```php
// tests/factories/user.factories.php
use App\Entity\User;

$fm->define(User::class)->setDefinitions([
    'email' => 'user{++}@example.com',
    'password' => 'password123',
    'roles' => ['ROLE_USER'],
    'createdAt' => fn() => new \DateTimeImmutable(),
]);
```

Using FixtureLoader in tests:

```php
use App\Fixture\FixtureLoader;
use App\Entity\User;

$loader = new FixtureLoader();

// Create single instance
$user = $loader->make(User::class);

// Create multiple instances
$users = $loader->makeMany(User::class, 10);

// With EntityManager for persistence
$em = $entityManager; // Doctrine EntityManager
$loader = new FixtureLoader($em);
$user = $loader->createAndPersist(User::class);
```

---

## 🔬 STEM CRUD: Architectural Patterns Spectrum

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                    THE ARCHITECTURAL SPECTRUM                                 ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║   ┌─────────────┐     ┌─────────────┐     ┌─────────────┐                ║
║   │   MVC       │     │   ADR       │     │   SAPI      │                ║
║   │ (Classic)   │ ←→  │ (API-First)│ ←→  │ (Async)     │                ║
║   ├─────────────┤     ├─────────────┤     ├─────────────┤                ║
║   │ • Full HTML │     │ • JSON:API  │     │ • AJAX      │                ║
║   │ • SSR       │     │ • REST      │     │ • SPA       │                ║
║   │ • Stateless │     │ • Stateless │     │ • Stateful  │                ║
║   └─────────────┘     └─────────────┘     └─────────────┘                ║
║         ↑                    ↑                    ↑                          ║
║         └──────────────────┴────────────────────┘                        ║
║                          ↓                                                 ║
║               ┌─────────────────────┐                                      ║
║               │      PWA           │                                      ║
║               │  (Progressive)     │                                      ║
║               ├─────────────────────┤                                      ║
║               │ • Offline-first     │                                      ║
║               │ • Installable       │                                      ║
║               │ • Web + Native     │                                      ║
║               └─────────────────────┘                                      ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝
```

### 1️⃣ MVC (Model-View-Controller) — Classic Web

**When to use:** Traditional server-rendered applications, SEO-critical pages.

```php
// Controller receives request, processes via Model, renders View
class UserController extends AbstractController
{
    public function index(): void
    {
        $users = $this->repository->findAll();
        $this->render('users/index', ['users' => $users]);
    }
}
```

| Pros | Cons |
|------|------|
| ✅ SEO-friendly | ❌ Page reloads |
| ✅ Simple architecture | ❌ Higher server load |
| ✅ Persistent state via sessions | ❌ Less interactive |
| ✅ Full page refresh | ❌ Slower UX |

### 2️⃣ ADR (Action-Domain-Responder) — API-First

**When to use:** Modern APIs, microservices, decoupled frontends.

```
╔════════════════════════════════════════════════════════════════╗
║                    ADR FLOW DIAGRAM                            ║
╠════════════════════════════════════════════════════════════════╣
║                                                                ║
║   Request ──▶ Action ──▶ Domain ──▶ Responder ──▶ Response   ║
║                    │          │            │                    ║
║                    ↓          ↓            ↓                    ║
║              Validation   Business    JSON:API                   ║
║                          Logic       Output                     ║
╚════════════════════════════════════════════════════════════════╝
```

**Our ADR Actions (JSON:API format):**

```php
// ListAction → GET /api/users
class ListAction
{
    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        $users = $this->loader->makeMany(User::class, 5);
        $resource = new Collection($users, new UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();
        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
```

**CRUD Endpoints:**

| Method | Endpoint | Action | Response | Use Case |
|--------|----------|--------|----------|----------|
| `GET` | `/api/users` | ListAction | `200 + JSON:API collection` | List all users |
| `GET` | `/api/users/{id}` | ShowAction | `200 + JSON:API item` | Get single user |
| `POST` | `/api/users` | CreateAction | `201 + JSON:API item` | Create new user |
| `PUT` | `/api/users/{id}` | UpdateAction | `200 + JSON:API item` | Full update (REST) |
| `PATCH` | `/api/users/{id}` | PatchAction | `200 + JSON:API item` | Partial update (AJAX) |
| `DELETE` | `/api/users/{id}` | DeleteAction | `204 No Content` | Delete user |

**PUT vs PATCH Example:**

```bash
# PUT - Full replacement (RESTful)
PUT /api/users/1
{
  "email": "new@example.com",
  "password": "secret",
  "roles": ["ROLE_USER"]
}

# PATCH - Partial update (AJAX/SPAs)
PATCH /api/users/1
{
  "email": "patched@example.com"
}
```

**JSON:API Response Format:**

```json
{
  "data": [
    {
      "type": "users",
      "id": "1",
      "attributes": {
        "email": "user@example.com",
        "roles": ["ROLE_USER"],
        "created_at": "2024-01-15T10:30:00Z"
      }
    }
  ],
  "meta": {
    "total": 25
  }
}
```

### 3️⃣ AJAX/SPA — Asynchronous Client

**When to use:** Real-time apps, dashboards, interactive UIs.

```
┌──────────────────────────────────────────────────────────────┐
│                    SPA ARCHITECTURE                          │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│   Browser (React/Vue/Svelte)                                 │
│        │                                                     │
│        ├── GET /api/users ──────────────▶ Oryx ADR API      │
│        │                                                     │
│        ├── POST /api/users ───────────▶ Oryx ADR API        │
│        │                                                     │
│        └── PATCH /api/users/1 ──────────▶ Oryx ADR API      │
│                                                              │
│   State Management (Redux/Pinia/Store)                      │
│        │                                                     │
│        ├── Optimistic Updates                                │
│        ├── Local Cache (React Query/SWR)                    │
│        └── IndexedDB (offline support)                       │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

**AJAX vs REST Payload Comparison:**

| Aspect | REST (PUT) | AJAX (PATCH) |
|--------|------------|--------------|
| **Method** | PUT | PATCH |
| **Payload** | Full user | Only changed fields |
| **Bandwidth** | 500 bytes | 50 bytes |
| **Example** | `{"id":1,"email":"a","roles":[],"password":"x"}` | `{"email":"new@example.com"}` |

### 4️⃣ PWA (Progressive Web App) — Native-Like

**When to use:** Mobile-first apps, offline-first requirements, installable apps.

```
┌──────────────────────────────────────────────────────────────┐
│                    PWA FEATURES                              │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│   ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│   │   Service   │  │   Web App   │  │   Manifest  │       │
│   │   Worker    │  │   Cache     │  │   JSON      │       │
│   ├─────────────┤  ├─────────────┤  ├─────────────┤       │
│   │ Background  │  │ Static +    │  │ Installable │       │
│   │ Sync        │  │ Dynamic     │  │ Home Screen │       │
│   │ Offline     │  │ Files       │  │ Icons       │       │
│   └─────────────┘  └─────────────┘  └─────────────┘       │
│                                                              │
│   manifest.json (auto-served at /manifest.json)             │
│   {                                                         │
│     "name": "Oryx ORM App",                                │
│     "short_name": "OryxApp",                               │
│     "display": "standalone",                                │
│     "start_url": "/"                                       │
│   }                                                         │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 📊 Pattern Comparison Matrix

| Aspect | MVC | ADR API | AJAX/SPA | PWA |
|--------|-----|---------|----------|-----|
| **Rendering** | Server | None | Client | Client |
| **State** | Stateless | Stateless | Stateful | Stateful |
| **SEO** | ✅ Excellent | ✅ With SSR | ❌ Needs SSR | ⚠️ Hybrid |
| **Performance** | ⚠️ Reload | ✅ Fast | ✅ Fast | ✅ Fastest |
| **Offline** | ❌ No | ❌ No | ⚠️ Limited | ✅ Full |
| **Complexity** | Low | Medium | High | Very High |
| **Bundle Size** | 0 KB | 0 KB | 100-500 KB | 100-500 KB |

### 🎯 When to Use Each Pattern

```
╔════════════════════════════════════════════════════════════════╗
║                DECISION FLOWCHART                            ║
╠════════════════════════════════════════════════════════════════╣
║                                                                ║
║   Need SEO + Simple? ──▶ Use MVC                            ║
║         │                                                    ║
║         ▼                                                    ║
║   API for Mobile/Native? ──▶ Use ADR                         ║
║         │                                                    ║
║         ▼                                                    ║
║   Real-time + Interactive? ──▶ Use AJAX/SPA                   ║
║         │                                                    ║
║         ▼                                                    ║
║   Offline + Installable? ──▶ Use PWA                          ║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
```

### 🔗 The Unified Stack (Our Web Skeleton)

```
┌─────────────────────────────────────────────────────────────────┐
│                    ORYX WEB SKELETON                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌─────────────────────────────────────────────────────────┐   │
│   │                    Frontend Layer                        │   │
│   │  ┌─────────┐  ┌─────────┐  ┌─────────┐               │   │
│   │  │   MVC   │  │   SPA   │  │   PWA   │               │   │
│   │  │ (Pages) │  │ (React) │  │ (Vue)   │               │   │
│   │  └────┬────┘  └────┬────┘  └────┬────┘               │   │
│   │       │              │              │                   │   │
│   └───────┼──────────────┼──────────────┼───────────────────┘   │
│           │              │              │                       │
│           └──────────────┴──────────────┘                       │
│                          │                                     │
│   ┌─────────────────────▼─────────────────────────────────┐  │
│   │                    ADR API Layer                          │  │
│   │  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐           │  │
│   │  │  List  │ │  Show  │ │ Create │ │Delete  │           │  │
│   │  └────────┘ └────────┘ └────────┘ └────────┘           │  │
│   │         │           │           │           │            │  │
│   └─────────┼───────────┼───────────┼───────────┼────────────┘  │
│             │           │           │           │              │
│   ┌─────────▼───────────▼───────────▼───────────▼────────────┐  │
│   │                    Domain Layer                          │  │
│   │  ┌─────────┐  ┌─────────┐  ┌─────────┐                │  │
│   │  │  User   │  │  Post   │  │  Group  │   Entity       │  │
│   │  └────┬────┘  └────┬────┘  └────┬────┘                │  │
│   │       │            │            │                       │  │
│   │  ┌────▼────────────▼────────────▼────┐                │  │
│   │  │        Doctrine ORM               │                │  │
│   │  │   EntityManager + UnitOfWork      │                │  │
│   │  └─────────────────┬────────────────┘                │  │
│   │                    │                                   │  │
│   └────────────────────┼───────────────────────────────────┘  │
│                        │                                        │
│   ┌────────────────────▼───────────────────────────────────┐  │
│   │                  Database Layer                          │  │
│   │  ┌──────────┐  ┌──────────┐  ┌──────────┐             │  │
│   │  │ MariaDB  │  │  MySQL  │  │ SQLite   │   (dev)    │  │
│   │  └──────────┘  └──────────┘  └──────────┘             │  │
│   └──────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 🧪 Development

```bash
# Install magical dependencies
composer install

# Cast tests
composer test

# Check spell syntax
composer cs-check

# Fix common typos
composer cs-fix

# Analyze magical abilities
composer stan
```

---

## 📜 License

MIT - Free as in magical freedom!

---

*"Muggles have invented something called the Internet, but wizards have Oryx."* — Albus Dumbledore
