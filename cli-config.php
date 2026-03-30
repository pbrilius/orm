<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Set error reporting
error_reporting(E_ALL);

// Require Composer autoloader using __DIR__ for correct path resolution
require_once __DIR__ . '/vendor/autoload.php';

// Import required classes
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;

// Create database connection for CLI tools
// Using SQLite in-memory for consistency with tests
$connectionParams = [
    'driver' => 'pdo_sqlite',
    'memory' => true,
];

$connection = DriverManager::getConnection($connectionParams);

// Create Doctrine ORM configuration
$doctrineConfig = new Configuration();

// Set up metadata driver using AnnotationReader
// Using __DIR__ to correctly locate potential entity directories if needed
$driver = new AnnotationDriver(
    new AnnotationReader(), 
    false
);
$doctrineConfig->setMetadataDriverImpl($driver);

// Proxy configuration - matching EntityManager.php settings
// Disabled auto-generation as requested, but keeping directory/namespace for completeness
$doctrineConfig->setAutoGenerateProxyClasses(
    \Doctrine\ORM\Proxy\ProxyFactory::AUTOGENERATE_NEVER
);
$doctrineConfig->setProxyDir(sys_get_temp_dir());
$doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

// Create EntityManager instance
$entityManager = \Doctrine\ORM\EntityManager::create($connection, $doctrineConfig);

// Return helper set for Doctrine console
return ConsoleRunner::createHelperSet($entityManager);