<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
use Doctrine\ORM\QueryBuilder;

class CoalitionExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (Coalition::class === $resourceClass) {
            $queryBuilder
                ->andWhere("${alias}.enabled = :true")
                ->setParameter('true', true)
            ;
        } elseif (Cause::class === $resourceClass) {
            $queryBuilder
                ->innerJoin("${alias}.coalition", 'coalition')
                ->leftJoin("${alias}.secondCoalition", 'coalition_2')
                ->andWhere('coalition.enabled = :true AND (coalition_2 IS NULL OR coalition_2.enabled = :true)')
                ->setParameter('true', true)
            ;
        }
    }
}
