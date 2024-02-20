<?php

namespace Tests\App\OpenAI\Client;

use App\OpenAI\Client\ClientInterface;
use App\OpenAI\Client\Resource\Message;
use App\OpenAI\Enum\MessageRoleEnum;
use App\OpenAI\Enum\RunStatusEnum;
use Faker\Factory;
use Faker\Generator;

class Client implements ClientInterface
{
    private Generator $faker;
    private ?string $lastRunId = null;
    private ?string $lastAssistantId = null;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function hasAssistant(string $assistantId): bool
    {
        return str_starts_with($assistantId, 'asst_');
    }

    public function createThread(): string
    {
        return uniqid('thread_');
    }

    public function createUserMessage(string $threadId, string $content): string
    {
        return uniqid('msg_');
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        $this->lastRunId = uniqid('run_');
        $this->lastAssistantId = $assistantId;

        return $this->lastRunId;
    }

    public function getRunStatus(string $threadId, string $runId): RunStatusEnum
    {
        return RunStatusEnum::COMPLETED;
    }

    public function cancelRun(string $threadId, string $runId): void
    {
        $this->lastRunId = null;
    }

    /**
     * @return Message[]|array
     */
    public function getMessages(string $threadId, int $limit = 10): array
    {
        return [
            new Message(
                $threadId,
                MessageRoleEnum::ASSISTANT,
                $this->faker->text('128'),
                [],
                new \DateTimeImmutable('now'),
                uniqid('msg_'),
                $this->lastAssistantId,
                $this->lastRunId
            ),
        ];
    }
}
