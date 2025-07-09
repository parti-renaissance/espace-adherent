<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\AuthoredItemsCollectionInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class AuthoredItemsCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $user = $this->security->getUser();

        if (
            !$user instanceof Adherent
            || !is_a($resourceClass, AuthoredItemsCollectionInterface::class, true)
        ) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->getRootAliases()[0].'.author = :author')
            ->setParameter('author', $user)
        ;
    }
}
