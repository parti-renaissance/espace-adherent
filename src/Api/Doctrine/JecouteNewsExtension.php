<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\News;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class JecouteNewsExtension implements ContextAwareQueryCollectionExtensionInterface
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
        string $operationName = null,
        array $context = []
    ) {
        if (News::class !== $resourceClass) {
            return;
        }

        if ($this->security->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')) {
            $alias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->andWhere(sprintf('%1$s.published = 1 AND %1$s.createdAt >= :date', $alias))
                ->orderBy("$alias.pinned", 'DESC')
                ->addOrderBy("$alias.createdAt", 'DESC')
                ->setParameter('date', (new \DateTime('-60 days'))->setTime(0, 0))
            ;

            if (!isset($context['filters']['with_enriched'])) {
                $queryBuilder
                    ->andWhere("$alias.enriched = :false")
                    ->setParameter('false', false)
                ;
            }
        }
    }
}
