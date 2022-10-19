<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\MunicipalEvent;
use Doctrine\ORM\QueryBuilder;

class MunicipalEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
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
        if (MunicipalEvent::class === $resourceClass) {
            $alias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->andWhere("$alias.status = :status")
                ->setParameter('status', BaseEvent::STATUS_SCHEDULED)
                ->andWhere("$alias.finishAt > :now")
                ->setParameter('now', new \DateTime())
            ;
        }
    }
}
