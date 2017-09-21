<?php

namespace AppBundle\Committee\Filter;

use AppBundle\Entity\Committee;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class CommitteeFilters
{
    const PER_PAGE = 20;

    const PARAMETER_OFFSET = 'o';
    const PARAMETER_PAGE = 'p';
    const PARAMETER_STATUS = 's';

    private $status;
    private $offset = 0;
    private $count = 0;

    final private function __construct()
    {
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public static function fromQueryString(Request $request)
    {
        $filters = new static();

        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, Committee::PENDING));
        $filters->setOffset($request->query->getInt(self::PARAMETER_OFFSET));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = trim($status);

        if ($status && !in_array($status, [Committee::PENDING, Committee::PRE_APPROVED, Committee::PRE_REFUSED], true)) {
            throw new \UnexpectedValueException(sprintf('Unexpected committee request status "%s".', $status));
        }

        if (empty($status)) {
            $this->status = null;

            return;
        }

        $this->status = $status;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        $qb
            ->andWhere(sprintf('%s.status = :status', $alias))
            ->setParameter('status', $this->getStatus())
        ;

        $qb
            ->orderBy(sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(sprintf('%s.name', $alias), 'ASC')
        ;

        // Get number of found committees
        $qbCount = clone $qb;
        $count = (int) $qbCount->select(sprintf('count(%s)', $alias))->getQuery()->getSingleScalarResult();
        $this->setCount($count);

        $qb
            ->setFirstResult($this->offset)
            ->setMaxResults(self::PER_PAGE)
        ;
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
        $parameters[self::PARAMETER_OFFSET] = $offset ?: $this->offset;

        return '?'.http_build_query($parameters);
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
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
}
