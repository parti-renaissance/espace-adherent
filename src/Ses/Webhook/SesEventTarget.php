<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesEventTarget
{
    public function __construct(
        public readonly int $messageId,
        public readonly int $adherentId,
    ) {
    }
}
