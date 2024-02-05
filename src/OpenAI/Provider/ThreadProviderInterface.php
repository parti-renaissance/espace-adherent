<?php

namespace App\OpenAI\Provider;

use App\OpenAI\Model\ThreadInterface;

interface ThreadProviderInterface
{
    public function findOneByIdentifier(string $identifier): ?ThreadInterface;

    public function refresh(ThreadInterface $thread): void;

    public function save(ThreadInterface $thread): void;
}
