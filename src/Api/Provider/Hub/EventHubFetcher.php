<?php

declare(strict_types=1);

namespace App\Api\Provider\Hub;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Operation;
use App\Api\Doctrine\EventExtension;
use App\Api\Filter\BoundingBoxFilter;
use App\Api\Filter\EventsDepartmentFilter;
use App\Api\Filter\InZoneOfScopeFilter;
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

    protected function buildQuery(array $filters, array $apiContext, ?Operation $operation): QueryBuilder
    {
        $queryBuilder = $this->eventRepository->createQueryBuilder('e');
        $queryNameGenerator = new QueryNameGenerator();

        $filterContext = $apiContext;
        $filterContext['filters'] = $filters;

        $this->boundingBoxFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->departmentFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);
        $this->scopeFilter->apply($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);

        $this->applyHubDateFilter($queryBuilder, $filters, 'e.beginAt');

        $this->eventExtension->applyToCollection($queryBuilder, $queryNameGenerator, Event::class, $operation, $filterContext);

        return $queryBuilder;
    }
}
