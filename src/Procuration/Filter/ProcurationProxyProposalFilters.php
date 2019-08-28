<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationProxyProposalFilters extends ProcurationFilters
{
    public const UNASSOCIATED = 'unassociated';
    public const ASSOCIATED = 'associated';
    public const DISABLED = 'disabled';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNASSOCIATED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if (!\in_array($status, [self::ASSOCIATED, self::UNASSOCIATED, self::DISABLED])) {
            throw new ProcurationException(sprintf('Unexpected procuration proxy proposal status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $status = $this->getStatus();

        $qb->andWhere("$alias.disabled = :disabled");

        if (self::UNASSOCIATED === $status) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        "$alias.frenchRequestAvailable = true",
                        "$alias.foreignRequestAvailable = true"
                    )
                )
                ->setParameter('disabled', false)
             ;
        } elseif (self::ASSOCIATED === $status) {
            $qb
                ->andWhere("$alias.frenchRequestAvailable = false")
                ->andWhere("$alias.foreignRequestAvailable = false")
                ->setParameter('disabled', false)
            ;
        } elseif (self::DISABLED === $status) {
            $qb->setParameter('disabled', true);
        }
    }
}
