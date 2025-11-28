<?php

declare(strict_types=1);

namespace Tests\App;

use App\Messenger\MessageRecorder\MessageRecorderInterface;

trait MessengerTestTrait
{
    abstract protected function getMessageRecorder(): MessageRecorderInterface;

    public function assertMessageIsDispatched(
        string $expectedMessageClass,
        bool $assert = true,
        string $help = '',
    ): bool {
        $found = false;

        foreach ($this->getMessageRecorder()->getMessages() as $envelope) {
            if (str_ends_with(\get_class($envelope->getMessage()), $expectedMessageClass)) {
                $found = true;
                break;
            }
        }

        if ($assert) {
            self::assertTrue($found, $help);
        }

        return $found;
    }

    public function assertMessageIsNotDispatched(string $unexpectedMessageClass): void
    {
        foreach ($this->getMessageRecorder()->getMessages() as $envelope) {
            if (is_a($envelope->getMessage(), $unexpectedMessageClass)) {
                self::fail("Message $unexpectedMessageClass found, but not expected.");
            }
        }
    }
}
