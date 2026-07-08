<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Poll\Poll;
use Doctrine\ORM\QueryBuilder;

class PollPublishedExtension implements QueryItemExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Poll::class, true)) {
            return;
        }

        $queryBuilder->andWhere(\sprintf('%s.published = true', $queryBuilder->getRootAliases()[0]));
    }
}
