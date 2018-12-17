<?php

namespace AppBundle\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use AppBundle\Entity\EnabledInterface;
use Doctrine\ORM\QueryBuilder;

class EnabledExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->modifyQuery($queryBuilder, $resourceClass);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (is_a($resourceClass, EnabledInterface::class, true)) {
            $queryBuilder
                ->andWhere(sprintf('%s.enabled = 1', $queryBuilder->getRootAliases()[0]))
            ;
        }
    }
}
