<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Chatbot\Thread;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class ChatbotAdherentThreadExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
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

        $this->modifyQuery($queryBuilder, $context);
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

        $this->modifyQuery($queryBuilder, $context);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $context): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $user = $this->security->getUser();

        $queryBuilder
            ->andWhere("$alias.adherent = :adherent")
            ->setParameter('adherent', $user)
            ->addOrderBy("$alias.updatedAt", 'DESC')
        ;
    }
}
