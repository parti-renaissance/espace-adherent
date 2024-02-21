<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventTypeEnum;
use App\Event\EventVisibilityEnum;
use App\Repository\Event\BaseEventRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class BaseEventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly BaseEventRepository $baseEventRepository,
        private readonly ScopeGeneratorResolver $scopeResolver
    ) {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::STATUSES, $operation->getName());
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        if (BaseEvent::class === $resourceClass) {
            $allowedTypes = [
                EventTypeEnum::TYPE_DEFAULT,
                EventTypeEnum::TYPE_COMMITTEE,
            ];

            $queryBuilder
                ->andWhere($alias.' INSTANCE OF :allowed_types')
                ->setParameter('allowed_types', $allowedTypes)
            ;
        }

        $scope = $this->scopeResolver->generate();

        if ($scope && $committeeUuids = $scope->getCommitteeUuids()) {
            $queryBuilder->andWhere(sprintf($alias.'.id IN (%s)', $queryBuilder->getEntityManager()->createQueryBuilder()
                ->select('ce.id')
                ->from(CommitteeEvent::class, 'ce')
                ->innerJoin('ce.committee', 'committee', Join::WITH, 'committee.uuid IN (:committee_uuids)')
                ->getDQL()
            ));
            $queryBuilder->setParameter('committee_uuids', $committeeUuids);
        }

        if ($this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')) {
            return;
        }

        $this->modifyQuery($queryBuilder, BaseEvent::ACTIVE_STATUSES, $operation->getName());

        /** @var $user Adherent */
        if ($this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')
            && ($user = $this->security->getUser()) instanceof Adherent) {
            if ($zone = $user->getParisBoroughOrDepartment()) {
                $this->baseEventRepository->withGeoZones(
                    [$zone],
                    $queryBuilder,
                    $alias,
                    BaseEvent::class,
                    'e3',
                    'zones',
                    'z3',
                    null,
                    true,
                    'z3_zone_parent'
                );
            }
        }
    }

    private function modifyQuery(QueryBuilder $queryBuilder, array $statuses, ?string $operationName = null): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        if (
            \in_array($operationName, [
                'api_base_events_get_public_item',
                'api_base_events_get_public_collection',
                'api_base_events_get_item',
                'api_base_events_get_collection',
            ])
            && !$this->security->getUser() instanceof Adherent
        ) {
            $queryBuilder
                ->andWhere("$alias.visibility != :private_visibility")
                ->setParameter('private_visibility', EventVisibilityEnum::PRIVATE)
            ;
        }

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->andWhere("$alias.status IN (:statuses)")
            ->setParameter('true', true)
            ->setParameter('statuses', $statuses)
        ;
    }
}
