<?php

namespace App\JeMengage\Hit\Command;

use App\Messenger\Message\AsynchronousMessageInterface;

class SaveAppHitCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $sessionId,
        public readonly array $data,
    ) {
    }
}
