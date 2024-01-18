<?php

namespace App\Messenger\Message;

interface LockableMessageInterface
{
    public function getLockKey(): string;

    public function getLockTtl(): int;

    public function isLockBlocking(): bool;
}
