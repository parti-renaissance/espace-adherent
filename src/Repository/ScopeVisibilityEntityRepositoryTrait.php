<?php

declare(strict_types=1);

namespace App\Repository;

use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\QueryBuilder;

trait ScopeVisibilityEntityRepositoryTrait
{
    protected function addScopeVisibility(QueryBuilder $queryBuilder, array $zones): void
    {
        if (empty($zones)) {
            $queryBuilder
                ->andWhere('campaign.visibility = :visibility')
                ->setParameter('visibility', ScopeVisibilityEnum::NATIONAL)
            ;

            return;
        }

        $queryBuilder
            ->andWhere('campaign.visibility = :visibility')
            ->setParameter('visibility', ScopeVisibilityEnum::LOCAL)
            ->innerJoin('campaign.zone', 'zone')
            ->leftJoin('zone.parents', 'zone_parent')
            ->andWhere('zone IN (:zones) OR zone_parent IN (:zones)')
            ->setParameter('zones', $zones)
        ;
    }
}
