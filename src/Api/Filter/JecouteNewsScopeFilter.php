<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use App\Scope\ScopeEnum;
use Doctrine\ORM\QueryBuilder;

final class JecouteNewsScopeFilter extends AbstractScopeFilter
{
    private ZoneRepository $zoneRepository;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, News::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        switch ($scopeGenerator->getCode()) {
            case ScopeEnum::NATIONAL:
                $queryBuilder->andWhere(sprintf('%s.space IS NULL', $alias));
                break;
        }
    }

    protected function getAllowedOperationNames(string $resourceClass): array
    {
        return ['_get_private_item', '_get_private_collection'];
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
