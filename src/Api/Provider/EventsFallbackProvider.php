<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\Filter\EventsDepartmentFilter;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;

class EventsFallbackProvider implements ProviderInterface
{
    public const CONTEXT_KEY = 'event_region_fallback';

    public function __construct(
        private readonly ProviderInterface $decorated,
        private readonly ZoneRepository $zoneRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $result = $this->decorated->provide($operation, $uriVariables, $context);

        $dptCode = $context['filters'][EventsDepartmentFilter::PROPERTY_NAME] ?? null;

        if (\is_array($dptCode)) {
            $dptCode = current($dptCode);
        }

        if (empty($dptCode)) {
            return $result;
        }

        /** @var Event[] $events */
        $events = iterator_to_array($result);

        $hasLocalEvent = false;
        foreach ($events as $event) {
            if (!$event->isNational()) {
                $hasLocalEvent = true;
                break;
            }
        }

        $region = $hasLocalEvent
            ? null
            : ($this->zoneRepository->findOneByCode($dptCode)?->getParentsOfType(Zone::REGION)[0] ?? null);

        if (!$region) {
            return $result instanceof PaginatorInterface
                ? new TraversablePaginator(new \ArrayIterator($events), $result->getCurrentPage(), $result->getItemsPerPage(), $result->getTotalItems())
                : $result;
        }

        $context['filters'][EventsDepartmentFilter::PROPERTY_NAME] = $region->getTypeCode();
        $context['request']->attributes->set(self::CONTEXT_KEY, true);

        return $this->decorated->provide($operation, $uriVariables, $context);
    }
}
