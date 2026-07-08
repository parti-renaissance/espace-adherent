<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class TimelineSessionResolver
{
    private const string CACHE_KEY_PREFIX = 'timeline_session.';
    private const int TTL = 900; // 15 minutes

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function resolve(Adherent $user, ?string $appSessionId): ?string
    {
        if (null !== $appSessionId) {
            return $appSessionId;
        }

        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX.$user->getId());
        if ($item->isHit()) {
            return $item->get();
        }

        $sessionId = Uuid::v4()->toRfc4122();

        if (!$this->cache->save($item->set($sessionId)->expiresAfter(self::TTL))) {
            return null;
        }

        $this->logger->info('Timeline: app did not send session_id, using fallback cursor.', [
            'user_id' => $user->getId(),
            'app_version' => $user->currentAppSession?->appVersion,
        ]);

        return $sessionId;
    }
}
