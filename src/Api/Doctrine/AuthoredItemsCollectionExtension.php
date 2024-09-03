<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\AuthoredItemsCollectionInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class AuthoredItemsCollectionExtension implements QueryCollectionExtensionInterface
{
    private Security $security;
    private bool $skip = false;

    public function __construct(Security $security)
    {
        $this->security = $security;
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
            || $this->skip
        ) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->getRootAliases()[0].'.author = :author')
            ->setParameter('author', $user)
        ;
    }

    public function setSkip(bool $skip): void
    {
        $this->skip = $skip;
    }
}
