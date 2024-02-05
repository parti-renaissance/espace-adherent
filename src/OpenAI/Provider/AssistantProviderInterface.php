<?php

namespace App\OpenAI\Provider;

use App\OpenAI\Model\AssistantInterface;

interface AssistantProviderInterface
{
    public function loadByIdentifier(string $identifier): ?AssistantInterface;

    public function refresh(AssistantInterface $assistant): void;
}
