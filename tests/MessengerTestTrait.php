<?php

namespace Tests\App;

use App\Messenger\MessageRecorder\MessageRecorderInterface;

trait MessengerTestTrait
{
    abstract protected function getMessageRecorder(): MessageRecorderInterface;

    public function assertMessageIsDispatched(string $expectedMessageClass, string $help = ''): void
    {
        $found = false;

        foreach ($this->getMessageRecorder()->getMessages() as $envelope) {
            if (is_a($envelope->getMessage(), $expectedMessageClass)) {
                $found = true;
                break;
            }
        }

        self::assertTrue($found, $help);
    }
}
