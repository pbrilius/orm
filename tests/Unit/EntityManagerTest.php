<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\DriverManager;
use Oryx\ORM\EntityManager;

class EntityManagerTest extends TestCase
{
    public function testEntityManagerCanBeCreated(): void
    {
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $conn = DriverManager::getConnection($connectionParams);
        $entityManager = new EntityManager($conn);

        $this->assertInstanceOf(EntityManager::class, $entityManager);
        $this->assertInstanceOf(\Doctrine\ORM\EntityManager::class, $entityManager->getDoctrineEntityManager());
    }

    public function testCreateQueryBuilderReturnsQueryBuilder(): void
    {
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $conn = DriverManager::getConnection($connectionParams);
        $entityManager = new EntityManager($conn);

        $queryBuilder = $entityManager->createQueryBuilder();

        $this->assertInstanceOf(\Oryx\ORM\QueryBuilder::class, $queryBuilder);
    }
}