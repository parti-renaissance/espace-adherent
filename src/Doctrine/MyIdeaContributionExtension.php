<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\IdeasWorkshop\Idea;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MyIdeaContributionExtension implements QueryCollectionExtensionInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if ('get_my_contributions' === $operationName && Idea::class === $resourceClass) {
            $alias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->leftJoin("$alias.answers", 'answer')
                ->leftJoin('answer.threads', 'thread')
                ->leftJoin('thread.comments', 'threadComment')
                ->andWhere('thread.author = :author OR threadComment.author = :author')
                ->setParameter('author', $this->tokenStorage->getToken()->getUser())
            ;
        }
    }
}
