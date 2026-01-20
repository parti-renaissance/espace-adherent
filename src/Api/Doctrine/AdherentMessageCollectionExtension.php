<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\QueryBuilder;

class AdherentMessageCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (AdherentMessage::class !== $resourceClass) {
            return;
        }

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%1$s.author = :author OR %1$s.teamOwner = :team_owner', $queryBuilder->getRootAliases()[0]))
            ->setParameter('author', $scope->getCurrentUser())
            ->setParameter('team_owner', $scope->getMainUser())
        ;
    }
}
