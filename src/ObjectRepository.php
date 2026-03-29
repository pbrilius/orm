<?php declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Oryx\ORM\EntityManager;
use Oryx\ORM\QueryBuilder;

class ObjectRepository
{
    private EntityManager $em;
    private string $entityName;
    private EntityRepository $repository;

    public function __construct(EntityManager $em, string $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->repository = $this->em->getDoctrineEntityManager()->getRepository($entityName);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function createQueryBuilder(string $alias = null): QueryBuilder
    {
        return new QueryBuilder($this->em->getDoctrineEntityManager()->createQueryBuilder($this->entityName, $alias));
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }
}