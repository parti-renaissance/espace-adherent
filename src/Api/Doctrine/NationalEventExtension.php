<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\NationalEventTypeEnum;
use Doctrine\ORM\QueryBuilder;

class NationalEventExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, NationalEvent::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.startDate >= :min_start_date")
            ->andWhere("$alias.type != :excluded_type")
            ->setParameter('min_start_date', new \DateTimeImmutable(((int) date('Y') - 1).'-01-01 00:00:00'))
            ->setParameter('excluded_type', NationalEventTypeEnum::JEM)
        ;
    }
}
