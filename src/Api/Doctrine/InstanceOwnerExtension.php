<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\InstanceOwnerInterface;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\QueryBuilder;

class InstanceOwnerExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, InstanceOwnerInterface::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, $context);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, InstanceOwnerInterface::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, $context);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $context): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%1$s.instanceKey = :instance_key', $alias))
            ->setParameter('instance_key', $scope->getInstanceKey())
        ;
    }
}
