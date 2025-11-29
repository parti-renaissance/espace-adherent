<?php

declare(strict_types=1);

namespace Tests\App\Chatbot\Provider;

use App\Chatbot\Provider\ProviderInterface;
use App\Chatbot\Provider\Resources\Message;
use Faker\Factory;
use Faker\Generator;

class DummyProvider implements ProviderInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function createThread(): string
    {
        return uniqid('thread_');
    }

    public function createMessage(string $threadId, string $role, string $content): string
    {
        return uniqid('message_');
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        return uniqid('run_');
    }

    public function cancelRun(string $threadId, string $runId): void
    {
    }

    public function getRunStatus(string $threadId, string $runId): string
    {
        return 'completed';
    }

    public function retrieveMessages(string $threadId, int $limit = 10): array
    {
        return [
            new Message(
                uniqid('message_'),
                'assistant',
                $this->faker->text('100'),
                new \DateTimeImmutable()
            ),
        ];
    }
}
