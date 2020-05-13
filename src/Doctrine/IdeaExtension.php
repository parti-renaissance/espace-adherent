<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\IdeasWorkshop\Idea;
use Doctrine\ORM\QueryBuilder;

class IdeaExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (Idea::class === $resourceClass && !isset($context['filters']['author.uuid'])) {
            $queryBuilder
                ->andWhere(sprintf('%s.publishedAt IS NOT NULL', $queryBuilder->getRootAliases()[0]))
            ;
        }
    }
}
