<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Event\BaseEventCategory;
use App\Entity\Event\EventCategory;
use Doctrine\ORM\QueryBuilder;

class BaseEventCategoryExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (is_subclass_of($resourceClass, BaseEventCategory::class)) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->join("$alias.eventGroupCategory", 'egc')
                ->andWhere("$alias.status = :status")
                ->andWhere('egc.status = :status')
                ->setParameters([
                    'status' => EventCategory::ENABLED,
                ])
            ;
        }
    }
}
