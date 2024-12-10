<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\Jecoute\News;
use App\Entity\Pap\Campaign;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\QueryBuilder;

final class ScopeVisibilityFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, EntityScopeVisibilityInterface::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
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
            if (ScopeEnum::LEGISLATIVE_CANDIDATE === $scope->getMainCode()) {
                $queryBuilder
                    ->leftJoin("$alias.zones", 'zone')
                    ->andWhere("$alias.visibility = :local AND zone IN (:zones)")
                    ->setParameter('local', ScopeVisibilityEnum::LOCAL)
                    ->setParameter('zones', $scope->getZones())
                ;

                return;
            }

            $queryBuilder
                ->leftJoin("$alias.zones", 'zone')
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
        ;

        if ($scope->getZones()) {
            $queryBuilder
                ->innerJoin("$alias.zone", 'zone')
                ->leftJoin('zone.parents', 'parent_zone')
                ->andWhere('zone IN (:zones) OR parent_zone IN (:zones)')
                ->setParameter('zones', $scope->getZones())
            ;
        }

        if (News::class === $resourceClass && $committeeUuids = $scope->getCommitteeUuids()) {
            $queryBuilder
                ->innerJoin("$alias.committee", 'committee')
                ->andWhere('committee.uuid IN (:committee_uuids)')
                ->setParameter('committee_uuids', $committeeUuids)
            ;
        }
    }

    protected function getAllowedOperationNames(string $resourceClass): array
    {
        if (is_a($resourceClass, News::class, true)) {
            return ['_api_/v3/jecoute/news/{uuid}_get', '_api_/v3/jecoute/news_get_collection'];
        }

        return ['{uuid}_get', '{uuid}{._format}_get', '_get_collection'];
    }
}
