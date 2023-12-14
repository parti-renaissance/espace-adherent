<?php

namespace App\OpenAI;

use App\OpenAI\Resources\Thread;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatbotClient
{
    public function __construct(
        private readonly Client $client,
        private readonly SessionInterface $session,
        private readonly string $openAIAssistantKey
    ) {
    }

    public function addMessage(string $chatbotCode, string $message): void
    {
        $threadId = $this->getCurrentThreadId($chatbotCode);

        $this->client->addMessageToThread($threadId, $message);

        $runId = $this->client->createRun($threadId, $this->openAIAssistantKey);

        $sessionKey = $this->buildChatbotRunIdKey($chatbotCode);
        $this->session->set($sessionKey, $runId);
    }

    public function getCurrentThread(string $chatbotCode): Thread
    {
        $threadId = $this->getCurrentThreadId($chatbotCode);
        $lastRunId = $this->getLastRunId($chatbotCode);

        return $this->client->getThread($threadId, $lastRunId);
    }

    public function clear(string $chatbotCode): void
    {
        $this->session->remove($this->buildChatbotThreadIdKey($chatbotCode));
        $this->session->remove($this->buildChatbotRunIdKey($chatbotCode));
    }

    private function getCurrentThreadId(string $chatbotCode): string
    {
        $sessionKey = $this->buildChatbotThreadIdKey($chatbotCode);

        $threadId = $this->session->get($sessionKey);

        if (!$threadId) {
            $threadId = $this->client->createThread();

            $this->session->set($sessionKey, $threadId);
        }

        return $threadId;
    }

    private function getLastRunId(string $chatbotCode): ?string
    {
        $sessionKey = $this->buildChatbotRunIdKey($chatbotCode);

        return $this->session->get($sessionKey);
    }

    private function buildChatbotThreadIdKey(string $chatbotCode): string
    {
        return sprintf('chatbot-%s-thread-id', $chatbotCode);
    }

    private function buildChatbotRunIdKey(string $chatbotCode): string
    {
        return sprintf('chatbot-%s-run-id', $chatbotCode);
    }
}
