<?php

namespace App\OpenAI\Client;

use App\OpenAI\Client\Resource\Message;
use App\OpenAI\Enum\RunStatusEnum;

interface ClientInterface
{
    public function hasAssistant(string $assistantId): bool;

    public function createThread(): string;

    public function createUserMessage(string $threadId, string $content): string;

    public function createRun(string $threadId, string $assistantId): string;

    public function getRunStatus(string $threadId, string $runId): RunStatusEnum;

    public function cancelRun(string $threadId, string $runId): void;

    /**
     * @return Message[]|array
     */
    public function getMessages(string $threadId, int $limit = 10): array;
}
