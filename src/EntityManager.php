<?php

declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
use League\Event\Emitter;
use League\Event\Event;
use League\Event\EmitterInterface;

class EntityManager
{
    private DoctrineEntityManager $em;
    private EmitterInterface $eventDispatcher;

    public function __construct(Connection $connection, array $config = [], ?EmitterInterface $eventDispatcher = null)
    {
        $doctrineConfig = new Configuration();
        $driver = $config['metadata.driver'] ?? new AnnotationDriver([], false);
        $doctrineConfig->setMetadataDriverImpl($driver);

        // Proxy configuration
        $doctrineConfig->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
        $doctrineConfig->setProxyDir(sys_get_temp_dir());
        $doctrineConfig->setProxyNamespace('Oryx\ORM\Proxy');

        $this->em = DoctrineEntityManager::create($connection, $doctrineConfig);
        $this->eventDispatcher = $eventDispatcher ?? new Emitter();
    }

    public function getDoctrineEntityManager(): DoctrineEntityManager
    {
        return $this->em;
    }

    public function getRepository(string $entityName): ObjectRepository
    {
        return new ObjectRepository($this, $entityName);
    }

    public function persist($entity): void
    {
        // Dispatch prePersist event
        $this->eventDispatcher->emit(new Event('orm.prePersist', [
            'entity' => $entity,
            'entityManager' => $this,
        ]));

        $this->em->persist($entity);

        // Dispatch postPersist event
        $this->eventDispatcher->emit(new Event('orm.postPersist', [
            'entity' => $entity,
            'entityManager' => $this,
        ]));
    }

    public function flush(): void
    {
        // Dispatch preFlush event
        $this->eventDispatcher->emit(new Event('orm.preFlush', [
            'entityManager' => $this,
        ]));

        $this->em->flush();

        // Dispatch postFlush event
        $this->eventDispatcher->emit(new Event('orm.postFlush', [
            'entityManager' => $this,
        ]));
    }

    public function clear(?string $entityName = null): void
    {
        // Dispatch preClear event
        $this->eventDispatcher->emit(new Event('orm.preClear', [
            'entityName' => $entityName,
            'entityManager' => $this,
        ]));

        $this->em->clear($entityName);

        // Dispatch postClear event
        $this->eventDispatcher->emit(new Event('orm.postClear', [
            'entityName' => $entityName,
            'entityManager' => $this,
        ]));
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->em->createQueryBuilder());
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return new UnitOfWork($this->em);
    }

    public function getEventDispatcher(): EmitterInterface
    {
        return $this->eventDispatcher;
    }
}
