<?php

declare(strict_types=1);

namespace App\Messenger\MessageRecorder;

use Symfony\Component\Messenger\Envelope;

interface MessageRecorderInterface
{
    public function record(Envelope $message): void;

    /**
     * @return Envelope[]|array
     */
    public function getMessages(): array;
}
