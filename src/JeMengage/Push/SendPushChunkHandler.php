<?php

declare(strict_types=1);

namespace App\JeMengage\Push;

use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\PushChunkNotification;
use App\JeMengage\Push\Command\SendPushChunkCommand;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendPushChunkHandler
{
    public function __construct(
        private readonly JeMarcheMessaging $messaging,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(SendPushChunkCommand $command): void
    {
        if ($this->cache->has($command->chunkKey)) {
            return;
        }

        if (empty($command->tokens)) {
            return;
        }

        $notification = new PushChunkNotification(
            $command->title,
            $command->body,
            $command->scope,
            $command->data,
            $command->notificationClassName,
            $command->pushNotificationUuid,
        );
        $notification->setTokens($command->tokens);

        $this->messaging->send($notification);

        $this->cache->set($command->chunkKey, true, 900);
    }
}
