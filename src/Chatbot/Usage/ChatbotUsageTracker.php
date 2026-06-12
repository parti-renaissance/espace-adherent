<?php

declare(strict_types=1);

namespace App\Chatbot\Usage;

use Symfony\Contracts\Service\ResetInterface;

class ChatbotUsageTracker implements ResetInterface
{
    private ?array $rawUsage = null;

    public function capture(array $rawUsage): void
    {
        $this->rawUsage = $rawUsage;
    }

    public function pull(): ?array
    {
        $rawUsage = $this->rawUsage;
        $this->rawUsage = null;

        return $rawUsage;
    }

    public function reset(): void
    {
        $this->rawUsage = null;
    }
}
