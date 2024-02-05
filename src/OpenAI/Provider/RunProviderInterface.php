<?php

namespace App\OpenAI\Provider;

use App\OpenAI\Model\RunInterface;

interface RunProviderInterface
{
    public function save(RunInterface $run): void;

    public function findOneByOpenAiId(string $openAiId): ?RunInterface;
}
