<?php

namespace App\OpenAI;

use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;

interface MessageFactoryInterface
{
    public function createAssistantMessage(
        ThreadInterface $thread,
        string $openAiId,
        string $text,
        array $annotations,
        \DateTimeInterface $date,
        ?AssistantInterface $assistant,
        ?RunInterface $run
    ): MessageInterface;
}
