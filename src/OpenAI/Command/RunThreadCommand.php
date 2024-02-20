<?php

namespace App\OpenAI\Command;

use App\Messenger\Message\LockableMessageInterface;

class RunThreadCommand implements LockableMessageInterface
{
    public function __construct(
        public readonly string $threadIdentifier,
        public readonly string $assistantIdentifier
    ) {
    }

    public function getLockKey(): string
    {
        return 'openai_thread_'.$this->threadIdentifier;
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
