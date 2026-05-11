<?php

declare(strict_types=1);

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use Doctrine\ORM\QueryBuilder;

final class EventBoundingBoxFilter extends AbstractFilter
{
    public const PROPERTY_NAME = 'bbox';

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
            || !\is_array($value)
        ) {
            return;
        }

        $northEastLatitude = $value['ne']['lat'] ?? null;
        $northEastLongitude = $value['ne']['lng'] ?? null;
        $southWestLatitude = $value['sw']['lat'] ?? null;
        $southWestLongitude = $value['sw']['lng'] ?? null;

        if (
            !is_numeric($northEastLatitude)
            || !is_numeric($northEastLongitude)
            || !is_numeric($southWestLatitude)
            || !is_numeric($southWestLongitude)
        ) {
            return;
        }

        $northEastLatitude = (float) $northEastLatitude;
        $northEastLongitude = (float) $northEastLongitude;
        $southWestLatitude = (float) $southWestLatitude;
        $southWestLongitude = (float) $southWestLongitude;

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(\sprintf(
                '%1$s.postAddress.latitude BETWEEN :bbox_min_lat AND :bbox_max_lat AND %1$s.postAddress.longitude BETWEEN :bbox_min_lng AND :bbox_max_lng',
                $alias
            ))
            ->setParameter('bbox_min_lat', min($northEastLatitude, $southWestLatitude))
            ->setParameter('bbox_max_lat', max($northEastLatitude, $southWestLatitude))
            ->setParameter('bbox_min_lng', min($northEastLongitude, $southWestLongitude))
            ->setParameter('bbox_max_lng', max($northEastLongitude, $southWestLongitude))
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        if (!is_a($resourceClass, Event::class, true)) {
            return [];
        }

        $corners = [
            'bbox[ne][lat]' => ['postAddress.latitude', 'North-east corner latitude of the bounding box.'],
            'bbox[ne][lng]' => ['postAddress.longitude', 'North-east corner longitude of the bounding box.'],
            'bbox[sw][lat]' => ['postAddress.latitude', 'South-west corner latitude of the bounding box.'],
            'bbox[sw][lng]' => ['postAddress.longitude', 'South-west corner longitude of the bounding box.'],
        ];

        $description = [];
        foreach ($corners as $name => [$property, $help]) {
            $description[$name] = [
                'property' => $property,
                'type' => 'number',
                'required' => false,
                'description' => $help,
                'openapi' => [
                    'description' => $help,
                ],
            ];
        }

        return $description;
    }
}
