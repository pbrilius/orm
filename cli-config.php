<?php
declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Oryx\ORM\Mapping\XmlDriver;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__ . '/.env');

// Set error reporting
error_reporting(E_ALL);

// Require Composer autoloader using __DIR__ for correct path resolution
require_once __DIR__ . '/vendor/autoload.php';

// Get database connection parameters from environment
$connectionParams = [
    'driver' => 'pdo_mysql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8mb4',
];

$connection = DriverManager::getConnection($connectionParams);

// Create Doctrine ORM configuration
$doctrineConfig = new Configuration();

// Set up metadata driver using our XML driver
$driver = new XmlDriver([
    'App\Entity' => __DIR__ . '/src/Schema/definitions',
]);
$doctrineConfig->setMetadataDriverImpl($driver);

// Proxy configuration - matching EntityManager.php settings
$doctrineConfig->setAutoGenerateProxyClasses(
    \Doctrine\ORM\Proxy\ProxyFactory::AUTOGENERATE_NEVER
);
$doctrineConfig->setProxyDir(sys_get_temp_dir());
$doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

// Create EntityManager instance
$entityManager = \Doctrine\ORM\EntityManager::create($connection, $doctrineConfig);

// Return helper set for Doctrine console
return ConsoleRunner::createHelperSet($entityManager);