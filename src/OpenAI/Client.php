<?php

namespace App\OpenAI;

use App\OpenAI\Resources\Message;
use App\OpenAI\Resources\Thread;
use OpenAI\Client as BaseClient;

class Client
{
    private BaseClient $openAI;

    public function __construct(string $openAIApiKey)
    {
        $this->openAI = \OpenAI::client($openAIApiKey);
    }

    public function createThread(): string
    {
        $threadResponse = $this->openAI->threads()->create([]);

        return $threadResponse->id;
    }

    public function addMessageToThread(string $threadId, string $message): void
    {
        $this->openAI->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $message,
        ]);
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        $runResponse = $this->openAI->threads()->runs()->create($threadId, [
            'assistant_id' => $assistantId,
        ]);

        return $runResponse->id;
    }

    public function getThread(string $threadId, string $lastRunId = null): Thread
    {
        $threadResponse = $this->openAI->threads()->retrieve($threadId);

        $lastRunStatus = $lastRunId
            ? $this->getRunStatus($threadId, $lastRunId)
            : null;

        return new Thread(
            $threadId,
            new \DateTime($threadResponse->createdAt),
            $lastRunStatus,
            $this->getThreadLastMessages($threadId)
        );
    }

    private function getRunStatus(string $threadId, string $runId): string
    {
        $runResponse = $this->openAI->threads()->runs()->retrieve($threadId, $runId);

        return $runResponse->status;
    }

    /**
     * @return Message[]|array
     */
    private function getThreadLastMessages(string $threadId, int $limit = 10)
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
                new \DateTime($messageResponse->createdAt),
                $messageResponse->role,
                $content
            );
        }

        return $messages;
    }
}
