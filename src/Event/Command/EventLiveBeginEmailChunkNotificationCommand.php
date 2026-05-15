<?php

declare(strict_types=1);

namespace App\Event\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class EventLiveBeginEmailChunkNotificationCommand extends UuidDefaultAsyncMessage implements EventNotificationCommandInterface
{
    public function __construct(
        Uuid $uuid,
        public readonly array $chunk,
        public readonly string $key,
    ) {
        parent::__construct($uuid);
    }
}
