<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Repository\Event\EventRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

final class EventsDepartmentFilter extends AbstractFilter
{
    public const PROPERTY_NAME = 'zone';

    private EventRepository $eventRepository;
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
            !is_a($resourceClass, Event::class, true)
            || self::PROPERTY_NAME !== $property
            || empty($value)
        ) {
            return;
        }

        if (!$zone = $this->zoneRepository->findOneBy(['code' => is_numeric($value) ? str_pad($value, 2, '0', \STR_PAD_LEFT) : $value, 'type' => [Zone::DEPARTMENT, Zone::CUSTOM]])) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $zoneQueryBuilder = $this->eventRepository->createGeoZonesQueryBuilder(
            [$zone],
            $queryBuilder,
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

        $queryBuilder->andWhere(\sprintf('(%s.id IN (%s) OR %s.national = 1)', $rootAlias, $zoneQueryBuilder->getDQL(), $rootAlias));
    }

    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];
        foreach ($this->properties as $property => $strategy) {
            $description[self::PROPERTY_NAME] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by zone code.',
                    'name' => self::PROPERTY_NAME,
                    'type' => 'string',
                ],
            ];
        }

        return $description;
    }

    #[Required]
    public function setEventRepository(EventRepository $eventRepository): void
    {
        $this->eventRepository = $eventRepository;
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}
