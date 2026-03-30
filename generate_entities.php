<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__ . '/.env');

// Set up database connection (same as cli-config.php)
$connectionParams = [
    'driver' => 'pdo_mysql',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => 'utf8mb4',
];

try {
    $connection = DriverManager::getConnection($connectionParams);
} catch (\Throwable $e) {
    fwrite(STDERR, "Could not connect to the database: " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "Using an in-memory SQLite database for metadata generation." . PHP_EOL);

    // Fallback to an in-memory SQLite database
    $connectionParams = [
        'driver' => 'pdo_sqlite',
        'memory' => true,
    ];
    $connection = DriverManager::getConnection($connectionParams);
}

// Create Doctrine ORM configuration
$doctrineConfig = new Configuration();

// Set up metadata driver using the SimplifiedXmlDriver from Doctrine
$xmlDriver = new SimplifiedXmlDriver([
    'App\Entity' => __DIR__ . '/src/Schema/definitions/App/Entity',
], '.orm.xml');
$doctrineConfig->setMetadataDriverImpl($xmlDriver);

// Proxy configuration - matching EntityManager.php settings
$doctrineConfig->setAutoGenerateProxyClasses(
    \Doctrine\ORM\Proxy\ProxyFactory::AUTOGENERATE_NEVER
);
$doctrineConfig->setProxyDir(sys_get_temp_dir());
$doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

// Create Doctrine EntityManager instance
$entityManager = DoctrineEntityManager::create($connection, $doctrineConfig);

// Get metadata factory from the Doctrine EntityManager
$metadataFactory = $entityManager->getMetadataFactory();

// Get all metadata
$metadatas = $metadataFactory->getAllMetadata();

if (empty($metadatas)) {
    fwrite(STDERR, "No metadata classes to process." . PHP_EOL);
    exit(1);
}

// Set up the entity generator
$generator = new EntityGenerator();
$generator->setUpdateEntityIfExists(false);
$generator->setGenerateStubMethods(true);
$generator->setRegenerateEntityIfExists(false);
$generator->setNumSpaces(4);

// Generate entities
$outputDir = __DIR__ . '/src/Entity';
foreach ($metadatas as $metadata) {
    $className = $metadata->getName();
    echo "Processing entity <info>$className</info>" . PHP_EOL;

    try {
        $generator->generate([$metadata], $outputDir, false);
        echo "  > Generated <info>$className</info>" . PHP_EOL;
    } catch (\Throwable $e) {
        fwrite(STDERR, "  > Failed to generate entity <info>$className</info>: " . $e->getMessage() . PHP_EOL);
    }
}

echo "Done." . PHP_EOL;