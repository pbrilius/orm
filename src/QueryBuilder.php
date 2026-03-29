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

    // SELECT operations
    public function select(string $dqlAlias = null): self
    {
        $this->qb->select($dqlAlias);
        return $this;
    }

    public function addSelect(string $dqlAlias): self
    {
        $this->qb->addSelect($dqlAlias);
        return $this;
    }

    // FROM operations
    public function from(string $class, string $alias = null, string $indexBy = null): self
    {
        $this->qb->from($class, $alias, $indexBy);
        return $this;
    }

    public function innerJoin(string $join, string $alias = null, string $condition = null, string $indexBy = null): self
    {
        $this->qb->innerJoin($join, $alias, $condition, $indexBy);
        return $this;
    }

    public function leftJoin(string $join, string $alias = null, string $condition = null, string $indexBy = null): self
    {
        $this->qb->leftJoin($join, $alias, $condition, $indexBy);
        return $this;
    }

    // WHERE operations
    public function where(string $where): self
    {
        $this->qb->where($where);
        return $this;
    }

    public function andWhere(string $where): self
    {
        $this->qb->andWhere($where);
        return $this;
    }

    public function orWhere(string $where): self
    {
        $this->qb->orWhere($where);
        return $this;
    }

    // PARAMETER operations
    public function setParameter($key, $value, $type = null): self
    {
        $this->qb->setParameter($key, $value, $type);
        return $this;
    }

    public function setParameters(array $params): self
    {
        $this->qb->setParameters($params);
        return $this;
    }

    // ORDER BY operations
    public function orderBy(string $sort, string $order = 'ASC'): self
    {
        $this->qb->orderBy($sort, $order);
        return $this;
    }

    public function addOrderBy(string $sort, string $order = 'ASC'): self
    {
        $this->qb->addOrderBy($sort, $order);
        return $this;
    }

    // LIMIT/OFFSET operations
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

    // GROUP BY operations
    public function groupBy(string $groupBy): self
    {
        $this->qb->groupBy($groupBy);
        return $this;
    }

    public function addGroupBy(string $groupBy): self
    {
        $this->qb->addGroupBy($groupBy);
        return $this;
    }

    // HAVING operations
    public function having(string $having): self
    {
        $this->qb->having($having);
        return $this;
    }

    public function andHaving(string $having): self
    {
        $this->qb->andHaving($having);
        return $this;
    }

    public function orHaving(string $having): self
    {
        $this->qb->orHaving($having);
        return $this;
    }

    // EXECUTION
    public function getQuery(): Query
    {
        return $this->qb->getQuery();
    }

    public function getDql(): string
    {
        return $this->qb->getDQL();
    }

    // Magic method proxy
    public function __call(string $name, array $arguments)
    {
        return $this->qb->$name(...$arguments);
    }
}