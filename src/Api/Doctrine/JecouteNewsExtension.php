<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class JecouteNewsExtension implements ContextAwareQueryCollectionExtensionInterface
{
    private Security $security;
    private ZoneRepository $zoneRepository;
    private RequestStack $requestStack;

    public function __construct(Security $security, ZoneRepository $zoneRepository, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->zoneRepository = $zoneRepository;
        $this->requestStack = $requestStack;
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
            $queryBuilder
                ->andWhere(sprintf('%1$s.published = 1 AND %1$s.createdAt >= :date', $queryBuilder->getRootAliases()[0]))
                ->setParameter('date', (new \DateTime('-60 days'))->setTime(0, 0))
            ;
        }
    }
}
