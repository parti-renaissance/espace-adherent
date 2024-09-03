<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\BaseEvent;
use App\Entity\Geo\Zone;
use App\Repository\Event\BaseEventRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

final class EventsDepartmentFilter extends AbstractFilter
{
    private BaseEventRepository $baseEventRepository;
    private ZoneRepository $zoneRepository;

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || 'zone' !== $property
            || empty($value)
        ) {
            return;
        }

        if (!$zone = $this->zoneRepository->findOneBy(['code' => is_numeric($value) ? str_pad($value, 2, '0', \STR_PAD_LEFT) : $value, 'type' => [Zone::DEPARTMENT, Zone::CUSTOM]])) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $this->baseEventRepository->withGeoZones(
            [$zone],
            $queryBuilder,
            $rootAlias,
            $resourceClass,
            'e2',
            'zones',
            'zip_code_filter_zone',
            function (QueryBuilder $zoneQueryBuilder, string $entityClassAlias) {
                $zoneQueryBuilder->andWhere(\sprintf('%s.published = 1', $entityClassAlias));
            },
            true,
            'zip_code_filter_zone_parent'
        );
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description['zone'] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by zone code.',
                    'name' => 'zone',
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    #[Required]
    public function setBaseEventRepository(BaseEventRepository $baseEventRepository): void
    {
        $this->baseEventRepository = $baseEventRepository;
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
