<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationRequestFilters extends ProcurationFilters
{
    const PARAMETER_STATUS = 'status';

    const PROCESSED = 'processed';
    const UNPROCESSED = 'unprocessed';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !in_array($status, [self::PROCESSED, self::UNPROCESSED], true)) {
            throw new ProcurationException(sprintf('Unexpected procuration request type "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        if (self::UNPROCESSED === $this->getStatus()) {
            $qb
                ->andWhere(sprintf('%s.processed = :flag AND %s.processedAt IS NULL', $alias, $alias))
                ->setParameter('flag', 0)
            ;
        } else {
            $qb
                ->andWhere(sprintf('%s.processed = :flag AND %s.processedAt IS NOT NULL', $alias, $alias))
                ->setParameter('flag', 1)
            ;
        }

        $qb
            ->orderBy(sprintf('%s.processed', $alias), 'ASC')
            ->addOrderBy(sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(sprintf('%s.lastName', $alias), 'ASC')
        ;
    }
}
