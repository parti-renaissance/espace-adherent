<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\AuthoredItemsCollectionInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class AuthoredItemsCollectionExtension implements QueryCollectionExtensionInterface
{
    private $security;
    private bool $skip = false;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
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
