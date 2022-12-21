<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\LocalSite\LocalSite;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

class LocalSiteScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, LocalSite::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);

        $queryBuilder
            ->andWhere("$alias.zone IN (:zones)")
            ->setParameter('zones', $scope->getZones())
        ;
    }
}
