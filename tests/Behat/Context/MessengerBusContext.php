<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\Messenger\MessageRecorder\MessageRecorderInterface;
use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert;
use Tests\App\MessengerTestTrait;

class MessengerBusContext extends RawMinkContext implements Context
{
    use MessengerTestTrait;

    private ?MessageRecorderInterface $messageRecorder = null;

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return $this->messageRecorder;
    }

    /**
     * @Then the message ":messageClass" should be dispatched
     */
    public function theMessageShouldBeDispatched(string $messageClass): void
    {
        $this->setUpMessageRecorder();

        Assert::assertTrue($this->assertMessageIsDispatched($messageClass, false));
    }

    private function setUpMessageRecorder(): void
    {
        $this->messageRecorder = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient()
            ->getContainer()
            ->get(MessageRecorderInterface::class)
        ;
    }
}
