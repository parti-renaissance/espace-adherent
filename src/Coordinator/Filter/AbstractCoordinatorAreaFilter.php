<?php

namespace App\Coordinator\Filter;

use App\Entity\Committee;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCoordinatorAreaFilter
{
    public const PER_PAGE = 20;
    public const PARAMETER_STATUS = 's';
    private const PARAMETER_OFFSET = 'o';

    protected $offset = 0;
    private $count = 0;
    private $status;

    final private function __construct()
    {
    }

    abstract protected function getAvailableStatus(): array;

    final public function apply(QueryBuilder $qb, string $alias): void
    {
        $qb
            ->andWhere(\sprintf('%s.status = :status', $alias))
            ->setParameter('status', $this->getStatus())

            ->orderBy(\sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(\sprintf('%s.name', $alias), 'ASC')

            ->setFirstResult($this->offset)
            ->setMaxResults(static::PER_PAGE)
        ;

        $this->updateCount($qb, $alias);
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public static function fromQueryString(Request $request)
    {
        $filter = new static();

        $filter->setStatus($request->query->get(self::PARAMETER_STATUS, Committee::PENDING));
        $filter->setOffset($request->query->getInt(self::PARAMETER_OFFSET));

        return $filter;
    }

    public function setStatus(string $status): void
    {
        $status = trim($status);

        if ($status && !\in_array($status, $this->getAvailableStatus(), true)) {
            throw new \UnexpectedValueException(\sprintf('Unexpected committee request status "%s".', $status));
        }

        if (empty($status)) {
            $this->status = null;

            return;
        }

        $this->status = $status;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getCount(): string
    {
        return $this->count;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function getQueryStringForOffset(?int $offset): string
    {
        $parameters = $this->getQueryStringParameters();
        $parameters[self::PARAMETER_OFFSET] = $offset ?? $this->offset;

        return '?'.http_build_query($parameters);
    }

    public function getPreviousPageQueryString(): string
    {
        $previousOffset = $this->offset - self::PER_PAGE;

        return $this->getQueryStringForOffset($previousOffset >= 0 ? $previousOffset : 0);
    }

    public function getNextPageQueryString(): string
    {
        return $this->getQueryStringForOffset($this->offset + self::PER_PAGE);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }

    private function updateCount(QueryBuilder $qb, string $alias): void
    {
        $qbCount = clone $qb;

        $count = $qbCount
            ->select(\sprintf('count(%s)', $alias))
            ->setMaxResults(null)
            ->setFirstResult(null)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->setCount($count);
    }
}
