<?php

namespace App\Telegram;

class Message
{
    public function __construct(
        public readonly string $chatId,
        public readonly string $text,
        public readonly array $entities,
        public readonly \DateTimeInterface $date
    ) {
    }
}
