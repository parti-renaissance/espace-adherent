<?php

namespace App\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use Doctrine\ORM\QueryBuilder;

final class OrderEventsBySubscriptionsFilter extends AbstractFilter
{
    private const PROPERTY_NAME = 'order';
    private const SUB_PROPERTY_NAME = 'subscriptions';

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ) {
        if (
            !is_a($resourceClass, Event::class, true)
            || self::PROPERTY_NAME !== $property
            || !\is_array($value)
            || !\array_key_exists(self::SUB_PROPERTY_NAME, $value)
        ) {
            return;
        }

        $order = \in_array(strtolower($value[self::SUB_PROPERTY_NAME]), ['desc', 'asc']) ? strtolower($value[self::SUB_PROPERTY_NAME]) : 'desc';

        $queryBuilder
            ->addSelect(\sprintf(
                '(%s) AS HIDDEN subscriptions_count',
                $queryBuilder->getEntityManager()->createQueryBuilder()
                    ->select('COUNT(1)')
                    ->from(EventRegistration::class, 'event_registration')
                    ->where('event_registration.event = '.$queryBuilder->getRootAliases()[0])
                    ->getDQL()
            ))
            ->addOrderBy('subscriptions_count', $order)
        ;
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::SUB_PROPERTY_NAME => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
