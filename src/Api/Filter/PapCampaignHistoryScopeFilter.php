<?php

declare(strict_types=1);

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Pap\CampaignHistory;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\QueryBuilder;

final class PapCampaignHistoryScopeFilter extends AbstractScopeFilter
{
    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, CampaignHistory::class, true);
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
                ->innerJoin("$alias.campaign", 'campaign')
                ->andWhere('campaign.visibility = :visibility')
                ->setParameter('visibility', ScopeVisibilityEnum::NATIONAL)
            ;
        } else {
            $queryBuilder
                ->innerJoin("$alias.building", 'building')
                ->innerJoin('building.address', 'address')
                ->innerJoin('address.zones', 'zone')
                ->leftJoin('zone.parents', 'parent_zone')
                ->andWhere('zone IN (:zones) OR parent_zone IN (:zones)')
                ->setParameter('zones', $scope->getZones())
            ;
        }
    }
}
