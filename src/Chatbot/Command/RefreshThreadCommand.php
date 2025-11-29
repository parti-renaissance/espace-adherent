<?php

declare(strict_types=1);

namespace App\Chatbot\Command;

use App\Messenger\Message\AbstractUuidMessage;
use App\Messenger\Message\LockableMessageInterface;

class RefreshThreadCommand extends AbstractUuidMessage implements LockableMessageInterface
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
