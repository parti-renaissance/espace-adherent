<?php

declare(strict_types=1);

namespace App\Chatbot\Provider;

use App\Chatbot\Provider\Resources\Message;
use OpenAI\Client;

class OpenAIProvider implements ProviderInterface
{
    private Client $openAI;

    public function __construct(string $openAIApiKey)
    {
        $this->openAI = \OpenAI::client($openAIApiKey);
    }

    public function createThread(): string
    {
        $threadResponse = $this->openAI->threads()->create([]);

        return $threadResponse->id;
    }

    public function createMessage(string $threadId, string $role, string $content): string
    {
        $messageResponse = $this->openAI->threads()->messages()->create($threadId, [
            'role' => $role,
            'content' => $content,
        ]);

        return $messageResponse->id;
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        $runResponse = $this->openAI->threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId,
        ]);

        return $runResponse->id;
    }

    public function cancelRun(string $threadId, string $runId): void
    {
        $this->openAI->threads()->runs()->cancel($threadId, $runId);
    }

    public function getRunStatus(string $threadId, string $runId): string
    {
        $runResponse = $this->openAI->threads()->runs()->retrieve($threadId, $runId);

        return $runResponse->status;
    }

    /**
     * @return Message[]|array
     */
    public function retrieveMessages(string $threadId, int $limit = 10): array
    {
        $messageListResponse = $this->openAI->threads()->messages()->list($threadId, [
            'limit' => $limit,
        ]);

        $messages = [];

        foreach ($messageListResponse->data as $messageResponse) {
            $content = $messageResponse->content[0]->text->value;

            if (!$content) {
                continue;
            }

            $messages[] = new Message(
                $messageResponse->id,
                $messageResponse->role,
                $content,
                new \DateTimeImmutable('@'.$messageResponse->createdAt)
            );
        }

        return $messages;
    }
}
