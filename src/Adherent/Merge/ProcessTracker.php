<?php

declare(strict_types=1);

namespace App\Adherent\Merge;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\Cache\CacheInterface;

class ProcessTracker
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HubInterface $hub,
    ) {
    }

    public function log(string $processId, string $message, int $percent): void
    {
        $logs = $this->getHistory($processId);

        $newEntry = [
            'timestamp' => time(),
            'message' => $message,
            'percent' => $percent,
        ];

        $logs[] = $newEntry;

        $cacheKey = $this->generateCacheKey($processId);
        $this->cache->delete($cacheKey);
        $item = $this->cache->getItem($cacheKey);
        $item->set($logs);
        $item->expiresAfter(3600);
        $this->cache->save($item);

        $update = new Update(
            'merge_status/'.$processId,
            json_encode($newEntry),
            true,
        );
        $this->hub->publish($update);
    }

    public function getHistory(string $processId): array
    {
        return $this->cache->get($this->generateCacheKey($processId), fn () => []);
    }

    public function clear(string $processId): void
    {
        $this->cache->delete($this->generateCacheKey($processId));
    }

    private function generateCacheKey(string $processId): string
    {
        return 'merge_logs_'.$processId;
    }
}
