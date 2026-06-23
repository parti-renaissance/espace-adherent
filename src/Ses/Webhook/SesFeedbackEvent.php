<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class SesFeedbackEvent
{
    /**
     * @param list<string> $recipients
     */
    public function __construct(
        public readonly SesFeedbackType $type,
        public readonly array $recipients,
    ) {
    }
}
