<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;

class GeoZoneExtension implements ContextAwareQueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (Zone::class === $resourceClass) {
            $queryBuilder
                ->andWhere(sprintf('%s.active = :true', $queryBuilder->getRootAliases()[0]))
                ->setParameter('true', true)
            ;
        }
    }
}
