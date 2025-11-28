<?php

declare(strict_types=1);

namespace App\Chatbot\Provider\Resources;

class Message
{
    private const ROLE_USER = 'user';

    public function __construct(
        public readonly string $id,
        public readonly string $role,
        public readonly string $content,
        public readonly \DateTimeInterface $createdAt,
    ) {
    }

    public function isUserMessage(): bool
    {
        return self::ROLE_USER === $this->role;
    }
}
