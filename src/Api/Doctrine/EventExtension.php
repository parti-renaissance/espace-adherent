<?php

namespace App\Api\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventVisibilityEnum;
use App\Repository\Event\BaseEventRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class EventExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly BaseEventRepository $baseEventRepository,
        private readonly ScopeGeneratorResolver $scopeResolver,
    ) {
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }

        $this->modifyQuery($queryBuilder);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (!is_a($resourceClass, BaseEvent::class, true)) {
            return;
        }
        $filters = $context['filters'] ?? [];

        $this->modifyQuery($queryBuilder, PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY] ? null : ($filters['status'] ?? BaseEvent::STATUS_SCHEDULED));

        $alias = $queryBuilder->getRootAliases()[0];

        if (PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            $scope = $this->scopeResolver->generate();

            if ($scope && $committeeUuids = $scope->getCommitteeUuids()) {
                $queryBuilder->andWhere(\sprintf($alias.'.id IN (%s)', $queryBuilder->getEntityManager()->createQueryBuilder()
                    ->select('ce.id')
                    ->from(CommitteeEvent::class, 'ce')
                    ->innerJoin('ce.committee', 'committee', Join::WITH, 'committee.uuid IN (:committee_uuids)')
                    ->getDQL()
                ));
                $queryBuilder->setParameter('committee_uuids', $committeeUuids);
            }

            return;
        }

        if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            /** @var Adherent $user */
            $user = $this->security->getUser();
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
        } else {
            $queryBuilder
                ->andWhere("$alias.visibility IN (:public_visibilities)")
                ->setParameter('public_visibilities', [EventVisibilityEnum::PUBLIC, EventVisibilityEnum::PRIVATE])
            ;
        }

        $queryBuilder
            ->addSelect("CASE WHEN $alias.beginAt >= NOW() THEN 1 ELSE 0 END AS HIDDEN is_future")
            ->addSelect("ABS(TIMESTAMPDIFF(SECOND, NOW(), $alias.beginAt)) AS HIDDEN time_to_begin")
            ->addOrderBy('is_future', 'DESC')
            ->addOrderBy('time_to_begin', 'ASC')
        ;
    }

    private function modifyQuery(QueryBuilder $queryBuilder, ?string $eventStatus = null): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere("$alias.published = :true")
            ->setParameter('true', true)
        ;

        if ($eventStatus) {
            $queryBuilder
                ->andWhere("$alias.status = :status")
                ->setParameter('status', $eventStatus)
            ;
        }
    }
}
