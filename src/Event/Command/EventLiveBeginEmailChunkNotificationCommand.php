<?php

namespace App\Event\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class EventLiveBeginEmailChunkNotificationCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly array $chunk,
        public readonly string $key,
    ) {
        parent::__construct($uuid);
    }
}
