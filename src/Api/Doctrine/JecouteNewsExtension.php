<?php

declare(strict_types=1);

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Jecoute\News;
use App\OAuth\Model\Scope;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class JecouteNewsExtension implements QueryCollectionExtensionInterface
{
    private Security $security;

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
        if (News::class !== $resourceClass) {
            return;
        }

        if ($this->security->isGranted(Scope::generateRole(Scope::JEMARCHE_APP))) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere(\sprintf('%1$s.published = 1 AND %1$s.createdAt >= :date', $alias))
                ->orderBy("$alias.createdAt", 'DESC')
                ->setParameter('date', new \DateTime('-60 days')->setTime(0, 0))
            ;
        }
    }
}
