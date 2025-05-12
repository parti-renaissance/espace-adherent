<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Agora;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class AgoraExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
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
        $this->modifyQuery($queryBuilder, $resourceClass, $context);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass, $context);
    }

    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass, array $context = []): void
    {
        if (Agora::class !== $resourceClass) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE !== $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            $queryBuilder
                ->andWhere("$alias.published = :true")
                ->setParameter('true', true)
            ;
        }

        $adherent = $this->getCurrentUser();

        if (
            $adherent
            && PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY]
        ) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder
                        ->expr()
                        ->orX()
                        ->add("$alias.president = :current_user")
                        ->add(":current_user MEMBER OF $alias.generalSecretaries")
                )
                ->setParameter('current_user', $adherent)
            ;
        }
    }

    private function getCurrentUser(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
    }
}
