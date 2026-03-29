<?php declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Proxy\ProxyFactory;

class EntityManager
{
    private DoctrineEntityManager $em;

    public function __construct(Connection $connection, array $config = [])
    {
        $doctrineConfig = new Configuration();
        $driver = $config['metadata.driver'] ?? new AnnotationDriver([], false);
        $doctrineConfig->setMetadataDriverImpl($driver);

        // Proxy configuration
        $doctrineConfig->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
        $doctrineConfig->setProxyDir(sys_get_temp_dir());
        $doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

        $this->em = DoctrineEntityManager::create($connection, $doctrineConfig);
    }

    public function getDoctrineEntityManager(): DoctrineEntityManager
    {
        return $this->em;
    }

    public function getRepository(string $entityName): EntityRepository
    {
        return $this->em->getRepository($entityName);
    }

    public function persist($entity): void
    {
        $this->em->persist($entity);
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function clear(string $entityName = null): void
    {
        $this->em->clear($entityName);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->em->createQueryBuilder());
    }
}