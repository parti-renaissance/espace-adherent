<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Geo\Zone;
use Doctrine\ORM\QueryBuilder;

class GeoZoneExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Zone::class === $resourceClass) {
            $queryBuilder
                ->andWhere(\sprintf('%s.active = :true', $queryBuilder->getRootAliases()[0]))
                ->setParameter('true', true)
            ;
        }
    }
}
