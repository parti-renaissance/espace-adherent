<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\Jecoute\News;
use App\Entity\Pap\Campaign;
use App\Scope\Generator\ScopeGeneratorInterface;
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

        if ($scope->isNational()) {
            $queryBuilder
                ->andWhere("$alias.visibility = :visibility")
                ->setParameter('visibility', ScopeVisibilityEnum::NATIONAL)
            ;

            return;
        }

        if (Campaign::class === $queryBuilder->getRootEntities()[0]) {
            $queryBuilder
                ->leftJoin("$alias.zone", 'zone')
                ->leftJoin('zone.parents', 'parent_zone')
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        "($alias.visibility = :local AND (zone IN (:zones) OR parent_zone IN (:zones)))",
                        "$alias.visibility = :national"
                    )
                )
                ->setParameter('local', ScopeVisibilityEnum::LOCAL)
                ->setParameter('national', ScopeVisibilityEnum::NATIONAL)
                ->setParameter('zones', $scope->getZones())
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

    protected function getAllowedOperationNames(string $resourceClass): array
    {
        if (is_a($resourceClass, News::class, true)) {
            return ['get_private'];
        }

        return ['get'];
    }
}
