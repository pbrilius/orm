<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Oryx\ORM\EntityManager;
use Oryx\ORM\QueryBuilder;

class QueryBuilderTest extends TestCase
{
    private EntityManager $entityManager;
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ];

        $conn = DriverManager::getConnection($connectionParams);
        $config = new Configuration();
        $driver = new AnnotationDriver([], false);
        $config->setMetadataDriverImpl($driver);
        $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Oryx\ORM\Proxy');

        $doctrineEm = DoctrineEntityManager::create($conn, $config);
        $this->entityManager = new EntityManager($conn);
        $this->queryBuilder = $this->entityManager->createQueryBuilder();
    }

    public function testSelectMethodReturnsQueryBuilder(): void
    {
        $result = $this->queryBuilder->select('u');
        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($this->queryBuilder, $result);
    }

    public function testFromMethodReturnsQueryBuilder(): void
    {
        $result = $this->queryBuilder->from('User::class', 'u');
        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($this->queryBuilder, $result);
    }

    public function testWhereMethodReturnsQueryBuilder(): void
    {
        $result = $this->queryBuilder->where('u.id = :id');
        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($this->queryBuilder, $result);
    }

    public function testSetParameterMethodReturnsQueryBuilder(): void
    {
        $result = $this->queryBuilder->setParameter('id', 1);
        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($this->queryBuilder, $result);
    }

    public function testOrderByMethodReturnsQueryBuilder(): void
    {
        $result = $this->queryBuilder->orderBy('u.name', 'ASC');
        $this->assertInstanceOf(QueryBuilder::class, $result);
        $this->assertSame($this->queryBuilder, $result);
    }

    public function testGetDqlReturnsString(): void
    {
        $this->queryBuilder
            ->select('u')
            ->from('User::class', 'u')
            ->where('u.id = :id')
            ->setParameter('id', 1)
            ->orderBy('u.name', 'ASC');

        $dql = $this->queryBuilder->getDql();
        $this->assertIsString($dql);
        $this->assertStringContainsString('SELECT u', $dql);
        $this->assertStringContainsString('FROM User::class u', $dql);
        $this->assertStringContainsString('WHERE u.id = :id', $dql);
        $this->assertStringContainsString('ORDER BY u.name ASC', $dql);
    }
}
