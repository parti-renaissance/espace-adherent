<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsDecorator(Aggregator::class)]
class CachedAggregator implements AggregatorInterface
{
    public function __construct(
        private readonly AggregatorInterface $decorated,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getStats(TargetTypeEnum $type, UuidInterface $objectUuid, bool $wait = false): StatsOutput
    {
        $cacheKey = \sprintf('hits_stats_%s_%s', $type->value, $objectUuid);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($type, $objectUuid, $wait) {
            $item->expiresAfter(300);

            return $this->decorated->getStats($type, $objectUuid, $wait);
        });
    }
}
