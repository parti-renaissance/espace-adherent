<?php

declare(strict_types=1);

namespace App\Chatbot\Agent;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\AI\Agent\AgentInterface;

final readonly class ChatbotAgentRegistry
{
    public function __construct(
        private ContainerInterface $agents,
    ) {
    }

    public function has(string $agentId): bool
    {
        return $this->agents->has($agentId);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function get(string $agentId): AgentInterface
    {
        if (!$this->agents->has($agentId)) {
            throw new \InvalidArgumentException(\sprintf('Unknown chatbot agent "%s".', $agentId));
        }

        return $this->agents->get($agentId);
    }
}
