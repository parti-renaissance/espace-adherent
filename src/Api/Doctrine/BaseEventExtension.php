<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Event\EventTypeEnum;
use Doctrine\ORM\QueryBuilder;

class BaseEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::STATUSES);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $this->modifyQuery($queryBuilder, BaseEvent::ACTIVE_STATUSES, $alias);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $statuses, string $alias = null): void
    {
        if (!$alias) {
            $alias = $queryBuilder->getRootAliases()[0];
        }

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->andWhere("$alias.status IN (:statuses)")
            ->andWhere("$alias NOT INSTANCE OF :institutional")
            ->setParameter('true', true)
            ->setParameter('statuses', $statuses)
            ->setParameter('institutional', EventTypeEnum::TYPE_INSTITUTIONAL)
        ;
    }
}
