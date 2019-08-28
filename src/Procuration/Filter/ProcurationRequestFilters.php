<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationRequestFilters extends ProcurationFilters
{
    public const PROCESSED = 'processed';
    public const UNPROCESSED = 'unprocessed';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !\in_array($status, [self::PROCESSED, self::UNPROCESSED], true)) {
            throw new ProcurationException(sprintf('Unexpected procuration request status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        if ($this->matchUnprocessedRequests()) {
            $qb->andWhere("$alias.processed = false AND $alias.processedAt IS NULL");
        } else {
            $qb->andWhere("$alias.processed = true AND $alias.processedAt IS NOT NULL");
        }
    }

    public function matchUnprocessedRequests(): bool
    {
        return self::UNPROCESSED === $this->getStatus();
    }
}
