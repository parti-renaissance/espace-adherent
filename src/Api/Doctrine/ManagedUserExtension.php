<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Projection\ManagedUser;
use Doctrine\ORM\QueryBuilder;

class ManagedUserExtension implements QueryItemExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (ManagedUser::class !== $resourceClass) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.status = :managed_user_status', $queryBuilder->getRootAliases()[0]))
            ->setParameter('managed_user_status', ManagedUser::STATUS_READY)
        ;
    }
}
