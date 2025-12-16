<?php

declare(strict_types=1);

namespace App\Adherent\Merge;

use App\Adherent\Command\AdherentMergeCommand;
use App\Entity\Adherent;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdherentMergeManager
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function handleMergeRequest(Adherent $adherentSource, Adherent $adherentTarget): void
    {
        if ($this->cache->hasItem($cacheKey = $this->generateCacheKey($adherentSource))) {
            throw new \RuntimeException('A merge operation is already in progress for this adherent (cache key: '.$cacheKey.').');
        }

        $this->messageBus->dispatch(new AdherentMergeCommand($adherentSource->getId(), $adherentTarget->getId()));

        $this->cache->save($this->cache->getItem($cacheKey)->set(true)->expiresAfter(3600));
    }

    public function generateCacheKey(Adherent $adherentSource): string
    {
        return 'admin-adherent-merge:'.$adherentSource->getId();
    }

    public function clearCache(Adherent $source): void
    {
        $this->cache->deleteItem($this->generateCacheKey($source));
    }

    public function mergeAlreadyStarted(Adherent $adherentSource): bool
    {
        return $this->cache->hasItem($this->generateCacheKey($adherentSource));
    }
}
