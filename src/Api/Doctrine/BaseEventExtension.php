<?php

namespace App\Api\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Event\EventTypeEnum;
use App\Repository\Event\BaseEventRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class BaseEventExtension implements QueryItemExtensionInterface, ContextAwareQueryCollectionExtensionInterface
{
    private Security $security;
    private AuthorizationCheckerInterface $authorizationChecker;
    private BaseEventRepository $baseEventRepository;

    public function __construct(
        Security $security,
        AuthorizationCheckerInterface $authorizationChecker,
        BaseEventRepository $baseEventRepository
    ) {
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
        $this->baseEventRepository = $baseEventRepository;
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->getRootAliases()[0].' NOT INSTANCE OF :institutional')
            ->setParameter('institutional', EventTypeEnum::TYPE_INSTITUTIONAL)
        ;

        $this->modifyQuery($queryBuilder, BaseEvent::STATUSES, $operationName);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ) {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        if (BaseEvent::class === $resourceClass && empty($context['filters']['group_source'])) {
            $allowedTypes = [
                EventTypeEnum::TYPE_DEFAULT,
                EventTypeEnum::TYPE_COMMITTEE,
                EventTypeEnum::TYPE_MUNICIPAL,
            ];

            if ($this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')) {
                $allowedTypes[] = EventTypeEnum::TYPE_INSTITUTIONAL;
            }

            $queryBuilder
                ->andWhere($queryBuilder->getRootAliases()[0].' INSTANCE OF :allowed_types')
                ->setParameter('allowed_types', $allowedTypes)
            ;
        } else {
            $queryBuilder
                ->andWhere($queryBuilder->getRootAliases()[0].' NOT INSTANCE OF :institutional')
                ->setParameter('institutional', EventTypeEnum::TYPE_INSTITUTIONAL)
            ;
        }

        if ($this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::ACTIVE_STATUSES, $operationName);

        /** @var $user Adherent */
        if ($this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')
            && $user = $this->security->getUser()) {
            $alias = $queryBuilder->getRootAliases()[0];
            if ($zone = $user->getParisBoroughOrDepartment()) {
                $this->baseEventRepository->withGeoZones(
                    [$zone],
                    $queryBuilder,
                    $alias,
                    BaseEvent::class,
                    'e3',
                    'zones',
                    'z3'
                );
            }
        }
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $statuses, string $operationName = null): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (\in_array($operationName, ['get_public', 'get'])
            && !$this->security->getUser() instanceof Adherent) {
            $queryBuilder->andWhere("$alias.private = false");
        }

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->andWhere("$alias.status IN (:statuses)")
            ->setParameter('true', true)
            ->setParameter('statuses', $statuses)
        ;
    }
}
