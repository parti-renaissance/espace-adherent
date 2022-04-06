<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Article;
use Doctrine\ORM\QueryBuilder;

class ArticleExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Article::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->join("$alias.category", 'category')
            ->andWhere("$alias.published = :published")
            ->andWhere('category.display = :displayed')
            ->setParameters([
                'published' => true,
                'displayed' => true,
            ])
        ;
    }
}
