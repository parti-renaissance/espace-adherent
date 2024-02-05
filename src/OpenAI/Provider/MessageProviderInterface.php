<?php

namespace App\OpenAI\Provider;

use App\OpenAI\Model\MessageInterface;

interface MessageProviderInterface
{
    public function save(MessageInterface $message): void;
}
