<?php

namespace App\OpenAI\Client;

use App\OpenAI\Client\Resource\Message;
use App\OpenAI\Enum\MessageRoleEnum;
use App\OpenAI\Enum\RunStatusEnum;
use OpenAI\Client as OpenAIClient;

class Client implements ClientInterface
{
    private OpenAIClient $openAI;

    public function __construct(private readonly string $openAIApiKey)
    {
        $this->openAI = \OpenAI::client($this->openAIApiKey);
    }

    public function createThread(): string
    {
        $threadResponse = $this->openAI->threads()->create([]);

        return $threadResponse->id;
    }

    public function createUserMessage(string $threadId, string $content): string
    {
        $messageResponse = $this->openAI->threads()->messages()->create($threadId, [
            'role' => MessageRoleEnum::USER,
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

    public function getRunStatus(string $threadId, string $runId): RunStatusEnum
    {
        $runResponse = $this->openAI->threads()->runs()->retrieve($threadId, $runId);

        return RunStatusEnum::tryFrom($runResponse->status) ?? RunStatusEnum::ERROR;
    }

    public function cancelRun(string $threadId, string $runId): void
    {
        $this->openAI->threads()->runs()->cancel($threadId, $runId);
    }

    /**
     * @return Message[]|array
     */
    public function getMessages(string $threadId, int $limit = 10): array
    {
        $messageListResponse = $this->openAI->threads()->messages()->list($threadId, [
            'limit' => $limit,
        ]);

        $messages = [];

        foreach ($messageListResponse->data as $messageResponse) {
            $role = MessageRoleEnum::tryFrom($messageResponse->role);

            if (!$role) {
                continue;
            }

            foreach ($messageResponse->content as $content) {
                if (!$text = $content->text->value) {
                    continue;
                }

                $messages[] = new Message(
                    $messageResponse->threadId,
                    $role,
                    $text,
                    $content->text->annotations,
                    new \DateTimeImmutable('@'.$messageResponse->createdAt),
                    $messageResponse->id,
                    $messageResponse->runId
                );
            }
        }

        return $messages;
    }
}
