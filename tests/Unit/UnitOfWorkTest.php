<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Oryx\ORM\EntityManager;
use Oryx\ORM\UnitOfWork;

class UnitOfWorkTest extends TestCase
{
    private EntityManager $entityManager;
    private UnitOfWork $unitOfWork;

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
        $this->unitOfWork = $this->entityManager->getUnitOfWork();
    }

    public function testUnitOfWorkIsCreated(): void
    {
        $this->assertInstanceOf(UnitOfWork::class, $this->unitOfWork);
        $this->assertInstanceOf(\Doctrine\ORM\EntityManager::class, $this->unitOfWork->getEntityManager());
    }

    public function testScheduleForInsert(): void
    {
        $entity = new \stdClass();
        $this->unitOfWork->scheduleForInsert($entity);
        $this->assertTrue($this->unitOfWork->isScheduledForInsert($entity));
    }

    public function testScheduleForUpdate(): void
    {
        $this->expectException(\Doctrine\ORM\ORMInvalidArgumentException::class);
        $entity = new \stdClass();
        $this->unitOfWork->scheduleForUpdate($entity);
    }

    public function testScheduleForDelete(): void
    {
        $entity = new \stdClass();
        $this->unitOfWork->scheduleForDelete($entity);
        $this->assertTrue($this->unitOfWork->isScheduledForDeletion($entity));
    }
}