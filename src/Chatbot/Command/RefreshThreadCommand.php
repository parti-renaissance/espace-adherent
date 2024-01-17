<?php

namespace App\Chatbot\Command;

use App\Messenger\Message\LockableMessageInterface;
use App\Messenger\Message\UuidDefaultAsyncMessage;

class RefreshThreadCommand extends UuidDefaultAsyncMessage implements LockableMessageInterface
{
    public function getLockKey(): string
    {
        return 'chatbot_thread_'.$this->getUuid()->toString();
    }

    public function getLockTtl(): int
    {
        return 5;
    }

    public function isLockBlocking(): bool
    {
        return false;
    }
}
