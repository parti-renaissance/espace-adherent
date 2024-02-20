<?php

namespace App\OpenAI\Model;

use App\OpenAI\Enum\RunStatusEnum;

interface RunInterface extends OpenAIResourceInterface
{
    public function getThread(): ThreadInterface;

    public function needRefresh(): bool;

    public function isInProgress(): bool;

    public function isCompleted(): bool;

    public function cancel(): void;

    public function updateOpenAiStatus(RunStatusEnum $openAiStatus): void;
}
