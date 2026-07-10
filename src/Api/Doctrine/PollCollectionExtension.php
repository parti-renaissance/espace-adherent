<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Poll\Poll;
use Doctrine\ORM\QueryBuilder;

class PollCollectionExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Poll::class, true)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(\sprintf('%s.published = true', $rootAlias))
            ->andWhere(\sprintf('%s.startAt <= :now', $rootAlias))
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy(\sprintf('%s.finishAt', $rootAlias), 'DESC')
        ;
    }
}
