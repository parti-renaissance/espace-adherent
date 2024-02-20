<?php

namespace App\OpenAI\Model;

interface AssistantInterface
{
    public function getIdentifier(): string;

    public function getOpenAiId(): string;
}
