# oryx/orm

DQL-centric ORM for PHP 8.0+ with seamless integration to oryx/mvc and oryx/adr architectures.

## Features

- Doctrine DBAL/ORM abstraction with enhanced DQL capabilities
- PSR-4 autoloading compliant
- League package integration (Config, Event, Pipeline, Tactician, etc.)
- Symfony Dotenv for environment configuration
- Full PSR-12 code style compliance
- Comprehensive test suite with PHPUnit, Mockery, PHPStan & Psalm

## Requirements

- PHP ^8.0
- Extensions: mbstring, intl, pdo_sqlite (for testing)

## Installation

```bash
composer require oryx/orm
```

## Basic Usage

```php
use Oryx\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;

// Configure connection
$connectionParams = [
    'dbname' => 'mydb',
    'user' => 'dbuser',
    'password' => 'dbpass',
    'host' => 'localhost',
    'driver' => 'pdo_mysql',
];

$conn = DriverManager::getConnection($connectionParams);
$entityManager = new EntityManager($conn);

// Create query builder
$queryBuilder = $entityManager->createQueryBuilder()
    ->select('u')
    ->from('User::class', 'u')
    ->where('u.status = :status')
    ->setParameter('status', 'active');

$users = $queryBuilder->getQuery()->getResult();
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
composer psalm

# Run tests
composer test
```

## License

MIT