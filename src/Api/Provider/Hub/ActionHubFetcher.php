<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use App\Api\Doctrine\ActionExtension;
use App\Api\Filter\BoundingBoxFilter;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Filter\MySubscribedActionsFilter;
use App\Api\Filter\OnlyMineFilter;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Repository\Action\ActionRepository;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\QueryBuilder;

class ActionHubFetcher extends AbstractHubFetcher
{
    public function __construct(
        private readonly ActionRepository $actionRepository,
        private readonly ZoneRepository $zoneRepository,
        private readonly BoundingBoxFilter $boundingBoxFilter,
        private readonly InZoneOfScopeFilter $scopeFilter,
        private readonly OnlyMineFilter $onlyMineFilter,
        private readonly MySubscribedActionsFilter $mySubscribedActionsFilter,
        private readonly ActionExtension $actionExtension,
    ) {
    }

    protected function entityType(): string
    {
        return HubItemRow::TYPE_ACTION;
    }

    protected function extractBeginAt(Event|Action $entity): ?\DateTimeInterface
    {
        \assert($entity instanceof Action);

        return $entity->date;
    }

    protected function extractFinishAt(Event|Action $entity): ?\DateTimeInterface
    {
        \assert($entity instanceof Action);

        return $entity->date;
    }

    protected function extractParticipantsCount(Event|Action $entity): int
    {
        \assert($entity instanceof Action);

        return $entity->getParticipantsCount();
    }

    protected function buildQuery(array $filters, array $apiContext, ?Operation $operation): QueryBuilder
    {
        $queryBuilder = $this->actionRepository->createQueryBuilder('a');
        $queryNameGenerator = new QueryNameGenerator();

        $filterContext = $apiContext;
        $filterContext['filters'] = $filters;

        $this->boundingBoxFilter->apply($queryBuilder, $queryNameGenerator, Action::class, $operation, $filterContext);
        $this->applyZoneFilter($queryBuilder, $filters);
        $this->scopeFilter->apply($queryBuilder, $queryNameGenerator, Action::class, $operation, $filterContext);
        $this->onlyMineFilter->apply($queryBuilder, $queryNameGenerator, Action::class, $operation, $filterContext);
        $this->mySubscribedActionsFilter->apply($queryBuilder, $queryNameGenerator, Action::class, $operation, $filterContext);

        $this->applyHubDateFilter($queryBuilder, $filters, 'a.date', 'beginAt');
        $this->applyHubFinishAtFilter($queryBuilder, $filters);

        $this->actionExtension->applyToCollection($queryBuilder, $queryNameGenerator, Action::class, $operation, $filterContext);

        return $queryBuilder;
    }

    private function applyZoneFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        $zoneCode = $filters['zone'] ?? null;

        if (\is_array($zoneCode)) {
            $zoneCode = current($zoneCode);
        }

        if (empty($zoneCode) || !\is_string($zoneCode)) {
            return;
        }

        $type = [Zone::DEPARTMENT, Zone::CUSTOM];

        if (str_contains($zoneCode, '_')) {
            [$type, $zoneCode] = explode('_', $zoneCode, 2);
        }

        $zone = $this->zoneRepository->findOneBy([
            'code' => is_numeric($zoneCode) ? str_pad($zoneCode, 2, '0', \STR_PAD_LEFT) : $zoneCode,
            'type' => $type,
        ]);

        if (!$zone) {
            return;
        }

        $zoneSubQueryBuilder = $this->actionRepository->createGeoZonesQueryBuilder(
            'a',
            [$zone],
            $queryBuilder,
            Action::class,
            'a_hub_zone',
            'zones',
            'hub_zone_filter_zone',
            null,
            true,
            'hub_zone_filter_zone_parent'
        );

        $queryBuilder->andWhere(\sprintf('EXISTS (%s)', $zoneSubQueryBuilder->getDQL()));
    }

    /**
     * Action has no real finishAt. We treat `Action.finishAt` as aligned on `Action.date` (zero duration)
     * so the filter stays predictable and explainable; clients that need an "in-flight" semantics should
     * combine `beginAt[*]` themselves.
     */
    private function applyHubFinishAtFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        $this->applyHubDateFilter($queryBuilder, $filters, 'a.date', 'finishAt');
    }
}
