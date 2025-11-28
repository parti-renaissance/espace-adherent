<?php

declare(strict_types=1);

namespace App\Chatbot\Provider;

interface ProviderInterface
{
    public function createThread(): string;

    public function createMessage(string $threadId, string $role, string $content): string;

    public function createRun(string $threadId, string $assistantId): string;

    public function cancelRun(string $threadId, string $runId): void;

    public function getRunStatus(string $threadId, string $runId): string;

    public function retrieveMessages(string $threadId, int $limit = 10): array;
}
