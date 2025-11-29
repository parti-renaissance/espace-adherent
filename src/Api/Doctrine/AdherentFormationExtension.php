<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class AdherentFormationExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Formation::class, true)) {
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
        if (!is_a($resourceClass, Formation::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, $context);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $context): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            /** @var Adherent $user */
            $user = $this->security->getUser();

            if ($zone = $user->getParisBoroughOrDepartment()) {
                $queryBuilder
                    ->leftJoin("$alias.zone", 'zone')
                    ->leftJoin('zone.parents', 'parent_zone')
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            "($alias.visibility = :local AND (zone = :zone OR parent_zone = :zone))",
                            "$alias.visibility = :national"
                        )
                    )
                    ->setParameter('zone', $zone)
                ;
            }
        }

        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE !== $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            $queryBuilder
                ->andWhere("$alias.published = :true")
                ->setParameter('true', true)
            ;
        }

        $queryBuilder
            ->addSelect("
                CASE
                    WHEN $alias.visibility = :local THEN 1
                    WHEN $alias.visibility = :national THEN 2
                    ELSE 3
                    END AS HIDDEN priority
            ")
            ->setParameter('local', ScopeVisibilityEnum::LOCAL)
            ->setParameter('national', ScopeVisibilityEnum::NATIONAL)
            ->orderBy('priority', 'ASC')
            ->addOrderBy("$alias.position", 'ASC')
        ;
    }
}
