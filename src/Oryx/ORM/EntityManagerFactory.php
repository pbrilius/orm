<?php
declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Factory for creating Oryx ORM EntityManager instances.
 */
class EntityManagerFactory
{
    /**
     * Create EntityManager from environment variables.
     */
    public static function createFromEnv(): EntityManager
    {
        $dotenv = new Dotenv();
        $dotenv->bootEnv(dirname(__DIR__, 2) . '/.env');

        return self::create([
            'driver' => 'pdo_mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'dbname' => $_ENV['DB_NAME'] ?? 'app',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
        ], dirname(__DIR__, 2) . '/src/Schema/definitions');
    }

    /**
     * Create EntityManager with custom configuration.
     */
    public static function create(array $connectionParams, string $schemaPath): EntityManager
    {
        $connection = DriverManager::getConnection($connectionParams);

        $config = new Configuration();

        $driver = new SimplifiedXmlDriver([
            $schemaPath => 'App\Entity',
        ], '.orm.xml');
        $config->setMetadataDriverImpl($driver);

        $config->setAutoGenerateProxyClasses(
            \Doctrine\ORM\Proxy\ProxyFactory::AUTOGENERATE_NEVER
        );
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Oryx\ORM\Proxy');

        return EntityManager::create($connection, $config);
    }

    /**
     * Create EntityManager with SQLite in-memory (for testing).
     */
    public static function createForTesting(string $schemaPath): EntityManager
    {
        return self::create([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $schemaPath);
    }
}