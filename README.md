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

## 🔮 RESTful vs AJAX: The Great Debate

### RESTful (Traditional Wizard)

```
GET    /api/users          → List all wizards
GET    /api/users/1        → Show wizard by ID
POST   /api/users          → Enroll new wizard
PUT    /api/users/1       → Update entire wizard record
DELETE /api/users/1       → Expel wizard
```

### AJAX/SPAs (Modern Sorcerer)

```
GET    /api/users          → List wizards (cached client-side)
PATCH  /api/users/1        → Change wizard's house only
```

| Aspect | RESTful | AJAX/SPAs |
|--------|---------|-----------|
| **State** | Client stores everything | Server manages state |
| **Payload** | Full resource | Only changes |
| **Caching** | HTTP cache | LocalStorage/IndexedDB |
| **Complexity** | Simpler protocol | Complex client |

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
