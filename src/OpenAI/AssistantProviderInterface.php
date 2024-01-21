<?php

namespace App\OpenAI;

interface AssistantProviderInterface
{
    public function loadByIdentifier(string $identifier): ?AssistantInterface;
}
