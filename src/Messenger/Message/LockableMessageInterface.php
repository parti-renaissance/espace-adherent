<?php

namespace App\Messenger\Message;

interface LockableMessageInterface
{
    public function getLockKey(): string;
}
