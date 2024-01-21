<?php

namespace App\OpenAI\Client\Resource;

use App\OpenAI\Enum\MessageRoleEnum;

class Message
{
    public function __construct(
        public readonly string $threadId,
        public readonly MessageRoleEnum $role,
        public readonly string $text,
        public readonly array $annotations,
        public readonly \DateTimeInterface $date,
        public readonly string $id,
        public readonly ?string $runId
    ) {
    }

    public function isUserMessage(): bool
    {
        return MessageRoleEnum::USER === $this->role;
    }
}
