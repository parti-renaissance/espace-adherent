<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Team\Team;
use App\Team\TypeEnum;
use Doctrine\ORM\QueryBuilder;

class PhoningTeamExtension implements QueryCollectionExtensionInterface
{
    public const ACTIONS = ['get_phoning_teams'];

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        $this->modifyQuery($queryBuilder, $resourceClass, $operationName);
    }

    public function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass, string $operationName): void
    {
        if (
            !is_a($resourceClass, Team::class, true)
            || !\in_array($operationName, self::ACTIONS, true)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.type = :type")
            ->setParameter('type', TypeEnum::PHONING)
        ;
    }
}
