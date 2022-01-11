<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\QueryBuilder;

final class ScopeVisibilityFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, EntityScopeVisibilityInterface::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        $scope = $scopeGenerator->generate($currentUser);

        if (\in_array($scope->getCode(), ScopeEnum::NATIONAL_SCOPES, true)) {
            $queryBuilder
                ->andWhere("$alias.visibility = :visibility")
                ->setParameter('visibility', ScopeVisibilityEnum::NATIONAL)
            ;

            return;
        }

        $queryBuilder
            ->andWhere("$alias.visibility = :visibility")
            ->setParameter('visibility', ScopeVisibilityEnum::LOCAL)
            ->innerJoin("$alias.zone", 'zone')
            ->leftJoin('zone.parents', 'parent_zone')
            ->andWhere('zone IN (:zones) OR parent_zone IN (:zones)')
            ->setParameter('zones', $scope->getZones())
        ;
    }
}
