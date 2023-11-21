<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\ZoneableEntity;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

class InZoneOfScopeFilter extends AbstractScopeFilter
{
    private EntityManagerInterface $entityManager;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, ZoneableEntity::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->entityManager
            ->getRepository($resourceClass)
            ->withGeoZones(
                $scopeGenerator->generate($currentUser)->getZones(),
                $queryBuilder,
                $alias,
                $resourceClass,
                'api_zone_filter_resource_alias',
                'zones',
                'api_zone_filter_zone_alias'
            )
        ;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
