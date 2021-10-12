<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AuthoredItemsCollectionInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class AuthoredItemsCollectionExtension implements QueryCollectionExtensionInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ) {
        if (
            !is_a($resourceClass, AuthoredItemsCollectionInterface::class, true)
            // Do not activate this extension on this operation since it's a custom logic regarding the author
            // @See App\Normalizer\AdherentMessageNormalizer
            || (is_a($resourceClass, AbstractAdherentMessage::class, true) && 'get' === $operationName)
        ) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->getRootAliases()[0].'.author = :author')
            ->setParameter('author', $this->security->getUser())
        ;
    }
}
