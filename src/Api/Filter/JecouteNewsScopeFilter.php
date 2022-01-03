<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\QueryBuilder;

final class JecouteNewsScopeFilter extends AbstractScopeFilter
{
    protected const OPERATION_NAMES = ['get_private'];

    private ZoneRepository $zoneRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, News::class, true)
            || !$this->needApplyFilter($property, $operationName)
        ) {
            return;
        }

        $scope = $this->getScopeGenerator($value)->getCode();
        $alias = $queryBuilder->getRootAliases()[0];

        if (ScopeEnum::NATIONAL === $scope) {
            $queryBuilder
                ->andWhere(sprintf('%s.space IS NULL', $alias))
            ;
        } elseif (ScopeEnum::REFERENT === $scope) {
            $queryBuilder
                ->andWhere(sprintf('%s.zone IN (:zones)', $alias))
                ->setParameter('zones', $this->zoneRepository->findForJecouteByReferentTags($this->getUser($value)->getManagedArea()->getTags()->toArray()))
            ;
        }
    }

    /**
     * @required
     */
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
