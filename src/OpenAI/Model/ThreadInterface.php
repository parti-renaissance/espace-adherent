<?php

namespace App\OpenAI\Model;

use Doctrine\Common\Collections\Collection;

interface ThreadInterface extends OpenAIResourceInterface
{
    public function getIdentifier(): string;

    public function hasCurrentRun(): bool;

    public function removeCurrentRun(): void;

    public function createCurrentRun(): void;

    public function getCurrentRun(): ?RunInterface;

    public function getMessagesToInitializeOnOpenAi(): Collection;

    public function hasMessageWithOpenAiId(string $openAiId): bool;
}
