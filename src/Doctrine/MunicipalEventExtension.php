<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\MunicipalEvent;
use Doctrine\ORM\QueryBuilder;

class MunicipalEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
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
        if (MunicipalEvent::class === $resourceClass) {
            $alias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->andWhere("$alias.status = :status")
                ->setParameter('status', MunicipalEvent::STATUS_SCHEDULED)
                ->andWhere("$alias.finishAt > :now")
                ->setParameter('now', new \DateTime())
            ;
        }
    }
}
