<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\QueryBuilder;

class JecouteRiposteExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
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

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Riposte::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.enabled = :true AND $alias.createdAt > :last_24")
            ->setParameter('true', true)
            ->setParameter('last_24', new \DateTime('-24 hours'))
        ;
    }
}
