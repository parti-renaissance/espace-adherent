<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CoalitionEvent;
use Doctrine\ORM\QueryBuilder;

class CoalitionEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        if (CoalitionEvent::class === $resourceClass) {
            $this->modifyQuery($queryBuilder);
        }
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (CoalitionEvent::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $this->modifyQuery($queryBuilder, $alias);

        if ('api_coalitions_events_get_subresource' === $operationName) {
            $queryBuilder
                ->andWhere("$alias.finishAt > :now")
                ->setParameter('now', new \DateTime())
            ;
        }
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $alias = null): void
    {
        if (!$alias) {
            $alias = $queryBuilder->getRootAliases()[0];
        }

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->andWhere("$alias.status IN (:statuses)")
            ->setParameter('true', true)
            ->setParameter('statuses', BaseEvent::ACTIVE_STATUSES)
        ;
    }
}
