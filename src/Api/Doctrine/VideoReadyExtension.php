<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use Doctrine\ORM\QueryBuilder;

class VideoReadyExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->restrictToReady($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->restrictToReady($queryBuilder, $resourceClass);
    }

    private function restrictToReady(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (!is_a($resourceClass, Video::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(\sprintf('%s.status = :video_status_ready', $alias))
            ->setParameter('video_status_ready', VideoStatusEnum::READY->value)
        ;
    }
}
