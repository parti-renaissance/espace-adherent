<?php

namespace App\OpenAI\Model;

interface OpenAIResourceInterface
{
    public function setOpenAiId(string $openAiId): void;

    public function hasOpenAiId(): bool;

    public function getOpenAiId(): ?string;
}
