<?php

namespace App\OpenAI\Provider;

use App\OpenAI\Model\AssistantInterface;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;

interface MessageProviderInterface
{
    public function save(MessageInterface $message): void;

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
