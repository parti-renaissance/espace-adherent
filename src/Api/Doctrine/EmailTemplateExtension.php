<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\EmailTemplate\EmailTemplate;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class EmailTemplateExtension implements QueryCollectionExtensionInterface
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
        if (!$this->supports($resourceClass)) {
            return;
        }
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$rootAlias.author = :author")
            ->setParameter('author', $this->security->getUser())
        ;
    }

    private function supports(string $resourceClass): bool
    {
        return EmailTemplate::class === $resourceClass;
    }
}
