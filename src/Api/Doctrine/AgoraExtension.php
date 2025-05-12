<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Agora;
use Doctrine\ORM\QueryBuilder;

class AgoraExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Agora::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->setParameter('true', true)
        ;
    }
}
