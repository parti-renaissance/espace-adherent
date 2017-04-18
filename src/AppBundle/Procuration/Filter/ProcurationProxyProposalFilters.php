<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationProxyProposalFilters extends ProcurationFilters
{
    const UNASSOCIATED = 'unassociated';
    const ASSOCIATED = 'associated';
    const DISABLED = 'disabled';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNASSOCIATED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if (!in_array($status, [self::ASSOCIATED, self::UNASSOCIATED, self::DISABLED])) {
            throw new ProcurationException(sprintf('Unexpected procuration proxy proposal type "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $status = $this->getStatus();
        if (self::UNASSOCIATED === $status) {
            $qb->andWhere(sprintf('%s.foundRequest IS NULL', $alias));
        } elseif (self::ASSOCIATED === $status) {
            $qb->andWhere(sprintf('%s.foundRequest IS NOT NULL', $alias));
        } elseif (self::DISABLED === $status) {
            $qb
                ->andWhere(sprintf('%s.disabled = :disabled', $alias))
                ->setParameter('disabled', 1)
            ;
        }

        $qb
            ->addOrderBy(sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(sprintf('%s.lastName', $alias), 'ASC')
            ->andWhere(sprintf('%s.reliability >= 0', $alias))
        ;
    }
}
