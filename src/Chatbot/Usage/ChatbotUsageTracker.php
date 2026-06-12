<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use App\Entity\Chatbot\Message;
use Symfony\Component\Messenger\MessageBusInterface;

class ChatbotUsageTracker
{
    private ?array $rawUsage = null;
    private ?float $startedAt = null;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly array $usageTrackedAgents = [],
    ) {
    }

    public function start(): void
    {
        $this->startedAt = microtime(true);
        $this->rawUsage = null;
    }

    public function capture(array $rawUsage): void
    {
        $this->rawUsage = $rawUsage;
    }

    public function record(Message $message, string $agentId): void
    {
        $rawUsage = $this->rawUsage;
        $startedAt = $this->startedAt;
        $this->rawUsage = null;
        $this->startedAt = null;

        if (!\in_array($agentId, $this->usageTrackedAgents, true)) {
            return;
        }

        $this->bus->dispatch(new RecordUsageCommand(
            $message->getId(),
            $rawUsage,
            null !== $startedAt ? (int) round((microtime(true) - $startedAt) * 1000) : null,
        ));
    }
}
