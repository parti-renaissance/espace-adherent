<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\Chatbot\Thread;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ChatbotAdherentThreadExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly GeneralScopeGenerator $scopeGenerator,
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
        if (!is_a($resourceClass, Thread::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Thread::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder);
    }

    private function modifyQuery(QueryBuilder $queryBuilder): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $user = $this->security->getUser();

        $allowedAgents = $user instanceof Adherent
            ? array_values(array_filter(array_map(
                [FeatureEnum::class, 'getAgentIdForFeature'],
                array_intersect(FeatureEnum::getChatbotFeatures(), $this->scopeGenerator->getAllAllowedFeatures($user))
            )))
            : [];

        $queryBuilder
            ->andWhere("$alias.adherent = :adherent")
            ->andWhere("$alias.agent IN (:allowedAgents)")
            ->setParameter('adherent', $user)
            ->setParameter('allowedAgents', $allowedAgents ?: ['__none__'])
            ->addOrderBy("$alias.updatedAt", 'DESC')
        ;
    }
}
