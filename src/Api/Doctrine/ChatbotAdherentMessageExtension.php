<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\Chatbot\Message;
use App\Scope\FeatureEnum;
use App\Scope\GeneralScopeGenerator;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ChatbotAdherentMessageExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly GeneralScopeGenerator $scopeGenerator,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, Message::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $user = $this->security->getUser();

        $allowedAgents = $user instanceof Adherent
            ? array_values(array_filter(array_map(
                [FeatureEnum::class, 'getAgentIdForFeature'],
                array_intersect(FeatureEnum::getChatbotFeatures(), $this->scopeGenerator->getAllAllowedFeatures($user))
            )))
            : [];

        $queryBuilder
            ->innerJoin("$alias.thread", 'thread')
            ->andWhere('thread.adherent = :adherent')
            ->andWhere('thread.agent IN (:allowedAgents)')
            ->setParameter('adherent', $user)
            ->setParameter('allowedAgents', $allowedAgents ?: ['__none__'])
        ;
    }
}
