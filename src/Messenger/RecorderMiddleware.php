<?php

namespace App\Messenger;

use App\Messenger\MessageRecorder\MessageRecorderInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class RecorderMiddleware implements MiddlewareInterface
{
    private $messageRecorder;

    public function __construct(MessageRecorderInterface $messageRecorder)
    {
        $this->messageRecorder = $messageRecorder;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->messageRecorder->record($envelope);

        return $stack->next()->handle($envelope, $stack);
    }
}
