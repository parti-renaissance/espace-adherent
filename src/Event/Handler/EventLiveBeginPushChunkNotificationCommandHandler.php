<?php

namespace App\Event\Handler;

use App\Entity\PushToken;
use App\Event\Command\EventLiveBeginPushChunkNotificationCommand;
use App\Firebase\JeMarcheMessaging;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;
use App\Repository\Event\EventRepository;
use App\Repository\PushTokenRepository;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EventLiveBeginPushChunkNotificationCommandHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly PushTokenRepository $pushTokenRepository,
        private readonly JeMarcheMessaging $messaging,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(EventLiveBeginPushChunkNotificationCommand $command): void
    {
        if ($this->cache->has($command->key)) {
            return;
        }

        if (!$event = $this->eventRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        $tokens = $this->findPushTokens($command->tokens);

        if (!empty($tokens)) {
            $notification = EventLiveBeginNotification::create($event);

            $notification->setTokens($tokens);

            $this->messaging->send($notification);
        }

        $this->cache->set($command->key, true, 900);
    }

    /** @return PushToken[] */
    private function findPushTokens(array $ids): array
    {
        return $this->pushTokenRepository
            ->createQueryBuilder('t')
            ->select('PARTIAL t.{id, identifier}')
            ->where('t.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }
}
