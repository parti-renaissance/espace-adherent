<?php

namespace App\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Event\EventTypeEnum;
use Doctrine\ORM\QueryBuilder;

final class EventsGroupSourceFilter extends AbstractContextAwareFilter
{
    private const PROPERTY_NAME = 'group_source';
    private const GROUP_SOURCE_EN_MARCHE = 'en_marche';
    private const GROUP_SOURCE_COALITIONS = 'coalitions';
    private const GROUP_SOURCES = [
        self::GROUP_SOURCE_EN_MARCHE,
        self::GROUP_SOURCE_COALITIONS,
    ];

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, BaseEvent::class, true)
            || self::PROPERTY_NAME !== $property
            || !\is_string($value)
            || !\in_array($value, self::GROUP_SOURCES)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(self::GROUP_SOURCE_COALITIONS === $value
                ? "($alias INSTANCE OF :cause OR $alias INSTANCE OF :coalition)"
                : "($alias NOT INSTANCE OF :cause AND $alias NOT INSTANCE OF :coalition)"
            )
            ->setParameter('cause', EventTypeEnum::TYPE_CAUSE)
            ->setParameter('coalition', EventTypeEnum::TYPE_COALITION)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Filter by group source ("en_marche" or "coalitions").',
                    'name' => self::PROPERTY_NAME,
                    'type' => 'string',
                ],
            ],
        ];
    }
}
