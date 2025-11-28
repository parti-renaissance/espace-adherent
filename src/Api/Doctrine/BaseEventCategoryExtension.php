<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\BaseEventCategory;
use Doctrine\ORM\QueryBuilder;

class BaseEventCategoryExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (is_subclass_of($resourceClass, BaseEventCategory::class)) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->join("$alias.eventGroupCategory", 'egc')
                ->andWhere("$alias.status = :status")
                ->andWhere('egc.status = :status')
                ->setParameters([
                    'status' => BaseEventCategory::ENABLED,
                ])
            ;
        }
    }
}
