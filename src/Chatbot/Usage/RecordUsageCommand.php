<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use App\Messenger\Message\AsynchronousMessageInterface;

readonly class RecordUsageCommand implements AsynchronousMessageInterface
{
    public function __construct(
        public int $messageId,
        public ?array $rawUsage,
        public ?int $responseTimeMs,
    ) {
    }
}
