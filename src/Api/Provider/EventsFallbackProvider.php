<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
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
        $filters = $context['filters'] ?? [];
        $dptCode = $filters[EventsDepartmentFilter::PROPERTY_NAME] ?? null;

        if (\is_array($dptCode)) {
            $dptCode = current($dptCode);
        }

        $result = $this->decorated->provide($operation, $uriVariables, $context);

        /** @var Event[] $events */
        $events = iterator_to_array($result);

        $hasLocal = false;
        foreach ($events as $event) {
            if (!$event->isNational()) {
                $hasLocal = true;
                break;
            }
        }

        if ($hasLocal || empty($dptCode)) {
            return $result;
        }

        $zone = $this->zoneRepository->findOneByCode($dptCode);
        $region = $zone?->getParentsOfType(Zone::REGION)[0] ?? null;

        if (!$region) {
            return $result;
        }

        $contextFallback = $context;
        $contextFallback['filters'] = $filters;
        $contextFallback['filters'][EventsDepartmentFilter::PROPERTY_NAME] = $region->getTypeCode();

        $context['request']->attributes->set(self::CONTEXT_KEY, true);

        return $this->decorated->provide($operation, $uriVariables, $contextFallback);
    }
}
