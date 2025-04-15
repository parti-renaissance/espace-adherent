<?php

namespace App\Event\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class EventLiveBeginPushChunkNotificationCommand extends UuidDefaultAsyncMessage implements EventNotificationCommandInterface
{
    public function __construct(
        UuidInterface $uuid,
        public readonly array $tokens,
        public readonly string $key,
    ) {
        parent::__construct($uuid);
    }
}
