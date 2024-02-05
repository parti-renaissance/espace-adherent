<?php

namespace App\OpenAI\Model;

interface MessageInterface extends OpenAIResourceInterface
{
    public function getThread(): ThreadInterface;

    public function getText(): string;
}
