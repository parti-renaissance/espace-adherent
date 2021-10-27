<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class JecouteNewsExtension implements ContextAwareQueryCollectionExtensionInterface
{
    private Security $security;
    private ZoneRepository $zoneRepository;

    public function __construct(Security $security, ZoneRepository $zoneRepository)
    {
        $this->security = $security;
        $this->zoneRepository = $zoneRepository;
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

        /** @var Adherent $user */
        $user = $this->security->getUser();
        if ($user->hasNationalRole()) {
            $queryBuilder
                ->andWhere(sprintf('%s.space IS NULL', $queryBuilder->getRootAliases()[0]))
            ;
        } elseif ($user->isReferent()) {
            $queryBuilder
                ->andWhere(sprintf('%s.zone IN (:zones)', $queryBuilder->getRootAliases()[0]))
                ->setParameter('zones', $this->zoneRepository->findForJecouteByReferentTags($user->getManagedArea()->getTags()->toArray()))
            ;
        }
    }
}
