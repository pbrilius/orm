<?php

declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\UnitOfWork as DoctrineUnitOfWork;
use SplObjectStorage;

class UnitOfWork
{
    private DoctrineEntityManager $em;
    private DoctrineUnitOfWork $doctrineUow;
    private SplObjectStorage $scheduledForInsert;
    private SplObjectStorage $scheduledForUpdate;
    private SplObjectStorage $scheduledForDeletion;

    public function __construct(DoctrineEntityManager $em)
    {
        $this->em = $em;
        $this->doctrineUow = $em->getUnitOfWork();
        $this->scheduledForInsert = new SplObjectStorage();
        $this->scheduledForUpdate = new SplObjectStorage();
        $this->scheduledForDeletion = new SplObjectStorage();
    }

    public function getEntityManager(): DoctrineEntityManager
    {
        return $this->em;
    }

    public function scheduleForInsert(object $entity): void
    {
        $this->scheduledForInsert->attach($entity);
        $this->doctrineUow->scheduleForInsert($entity);
    }

    public function scheduleForUpdate(object $entity): void
    {
        $this->scheduledForUpdate->attach($entity);
        $this->doctrineUow->scheduleForUpdate($entity);
    }

    public function scheduleForDelete(object $entity): void
    {
        $this->scheduledForDeletion->attach($entity);
        $this->doctrineUow->scheduleForDelete($entity);
    }

    public function isScheduledForInsert(object $entity): bool
    {
        return $this->scheduledForInsert->contains($entity);
    }

    public function isScheduledForUpdate(object $entity): bool
    {
        return $this->scheduledForUpdate->contains($entity);
    }

    public function isScheduledForDeletion(object $entity): bool
    {
        return $this->scheduledForDeletion->contains($entity);
    }

    public function computeChangeSets(): void
    {
        $this->doctrineUow->computeChangeSets();
    }

    public function commit(): void
    {
        $this->doctrineUow->commit();
    }

    public function getEntityChangeSet(object $entity): array
    {
        return $this->doctrineUow->getEntityChangeSet($entity);
    }

    public function getOriginalEntityData(object $entity): array
    {
        return $this->doctrineUow->getOriginalEntityData($entity);
    }
}
