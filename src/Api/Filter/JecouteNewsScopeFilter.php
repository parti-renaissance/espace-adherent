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

    protected function needApplyFilter(string $property, string $resourceClass, string $operationName = null): bool
    {
        return is_a($resourceClass, News::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];
        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $currentUser;

        switch ($scopeGenerator->getCode()) {
            case ScopeEnum::NATIONAL:
                $queryBuilder->andWhere(sprintf('%s.space IS NULL', $alias));
                break;
            case ScopeEnum::REFERENT:
                $queryBuilder
                    ->andWhere(sprintf('%s.zone IN (:zones)', $alias))
                    ->setParameter(
                        'zones',
                        $this->zoneRepository->findForJecouteByReferentTags($user->getManagedArea()->getTags()->toArray())
                    )
                ;
                break;
        }
    }

    protected function getAllowedOperationNames(): array
    {
        return ['get_private'];
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
