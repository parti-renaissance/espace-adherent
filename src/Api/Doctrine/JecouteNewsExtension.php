<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\News;
use Doctrine\ORM\QueryBuilder;

class JecouteNewsExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (News::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.published = 1', $queryBuilder->getRootAliases()[0]))
            ;
        }
    }
}
