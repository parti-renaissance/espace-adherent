<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Pap\VotePlace;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class PapVotePlaceScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, VotePlace::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $scope = $scopeGenerator->generate($currentUser);
        if (!$scope->isNational()) {
            $queryBuilder
                ->innerJoin($alias.'.zone', 'zone')
                ->leftJoin('zone.parents', 'parent_zone')
                ->andWhere('zone IN (:zones) OR parent_zone IN (:zones)')
                ->setParameter('zones', $scope->getZones())
            ;
        }
    }
}
