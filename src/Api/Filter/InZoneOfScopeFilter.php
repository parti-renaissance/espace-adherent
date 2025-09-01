<?php

namespace App\Api\Filter;

use App\Entity\Adherent;
use App\Entity\ZoneableEntityInterface;
use App\Repository\Geo\ZoneRepository;
use App\Scope\Generator\ScopeGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

class InZoneOfScopeFilter extends AbstractScopeFilter
{
    private EntityManagerInterface $entityManager;
    private ZoneRepository $zoneRepository;

    protected function needApplyFilter(string $property, string $resourceClass): bool
    {
        return is_a($resourceClass, ZoneableEntityInterface::class, true);
    }

    protected function applyFilter(
        QueryBuilder $queryBuilder,
        Adherent $currentUser,
        ScopeGeneratorInterface $scopeGenerator,
        string $resourceClass,
        array $context,
    ): void {
        $alias = $queryBuilder->getRootAliases()[0];

        $this
            ->entityManager
            ->getRepository($resourceClass)
            ->withGeoZones(
                $this->getScopeZones($currentUser, $scopeGenerator),
                $queryBuilder,
                $alias,
                $resourceClass,
                'api_zone_filter_resource_alias',
                $resourceClass::getZonesPropertyName(),
                'api_zone_filter_zone_alias',
                [$resourceClass, 'alterQueryBuilderForZones']
            )
        ;
    }

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }

    private function getScopeZones(Adherent $currentUser, ScopeGeneratorInterface $scopeGenerator): array
    {
        $scope = $scopeGenerator->generate($currentUser);

        if ($scope->getCommitteeUuids()) {
            return $this->zoneRepository->findZonesByCommitteesUuids($scope->getCommitteeUuids());
        }

        return $scope->getZones();
    }
}
