<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use App\Api\Doctrine\EventExtension;
use App\Api\Filter\BoundingBoxFilter;
use App\Api\Filter\EventsDepartmentFilter;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Filter\MySubscribedEventsFilter;
use App\Api\Filter\OnlyMineFilter;
use App\Entity\Action\Action;
use App\Entity\Event\Event;
use App\Repository\Event\EventRepository;
use Doctrine\ORM\QueryBuilder;

class EventHubFetcher extends AbstractHubFetcher
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly BoundingBoxFilter $boundingBoxFilter,
        private readonly EventsDepartmentFilter $departmentFilter,
        private readonly InZoneOfScopeFilter $scopeFilter,
        private readonly OnlyMineFilter $onlyMineFilter,
        private readonly MySubscribedEventsFilter $mySubscribedEventsFilter,
        private readonly EventExtension $eventExtension,
    ) {
    }

    protected function entityType(): string
    {
        return HubItemRow::TYPE_EVENT;
    }

    protected function extractBeginAt(Event|Action $entity): ?\DateTimeInterface
    {
        \assert($entity instanceof Event);

        return $entity->getBeginAt();
    }

    protected function extractFinishAt(Event|Action $entity): ?\DateTimeInterface
    {
        \assert($entity instanceof Event);

        return $entity->getFinishAt();
    }

    protected function extractParticipantsCount(Event|Action $entity): int
    {
        \assert($entity instanceof Event);

        return $entity->getParticipantsCount();
    }

    protected function buildQuery(array $filters, array $apiContext, ?Operation $operation): QueryBuilder
    {
        $queryBuilder = $this->eventRepository->createQueryBuilder('e');
        $queryNameGenerator = new QueryNameGenerator();

        $filterContext = $apiContext;
        $filterContext['filters'] = $filters;

        // Hub-item is a public aggregation feed — cancelled events must never surface,
        // regardless of any `?status=` override the caller might attempt.
        unset($filterContext['filters']['status']);

        $this->boundingBoxFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->departmentFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->scopeFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->onlyMineFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->mySubscribedEventsFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);

        $this->applyHubDateFilter($queryBuilder, $filters, 'e.beginAt', 'beginAt');
        $this->applyHubDateFilter($queryBuilder, $filters, 'e.finishAt', 'finishAt');

        $this->eventExtension->applyToCollection($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);

        return $queryBuilder;
    }
}
