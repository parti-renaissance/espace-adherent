<?php

namespace AppBundle\Messenger\MessageRecorder;

use Symfony\Component\Messenger\Envelope;

class MessageRecorder implements MessageRecorderInterface
{
    private $messages = [];

    public function record(Envelope $message): void
    {
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
