<?php declare(strict_types=1);

namespace Oryx\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;

class QueryBuilder
{
    private DoctrineQueryBuilder $qb;

    public function __construct(DoctrineQueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function select(string $dqlAlias = null): self
    {
        $this->qb->select($dqlAlias);
        return $this;
    }

    public function from(string $class, string $alias = null, string $indexBy = null): self
    {
        $this->qb->from($class, $alias, $indexBy);
        return $this;
    }

    public function where(string $where): self
    {
        $this->qb->where($where);
        return $this;
    }

    public function setParameter($key, $value, $type = null): self
    {
        $this->qb->setParameter($key, $value, $type);
        return $this;
    }

    public function orderBy(string $sort, string $order = 'ASC'): self
    {
        $this->qb->orderBy($sort, $order);
        return $this;
    }

    public function setFirstResult(int $offset): self
    {
        $this->qb->setFirstResult($offset);
        return $this;
    }

    public function setMaxResults(int $limit): self
    {
        $this->qb->setMaxResults($limit);
        return $this;
    }

    public function getQuery(): Query
    {
        return $this->qb->getQuery();
    }

    public function getDql(): string
    {
        return $this->qb->getDQL();
    }

    public function __call(string $name, array $arguments)
    {
        return $this->qb->$name(...$arguments);
    }
}