<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\ProcurationSendReminderCommand;
use AppBundle\Mailer\Message\ProcurationProxyReminderMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCommand()
    {
        $this->decorated = false;
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);

        $this->assertContains('1 reminders sent', $output);
        $this->assertCountMails(1, ProcurationProxyReminderMessage::class);
        $this->assertCount(1, $this->getProcurationRequestRepository()->findBy(['reminded' => 1]), 'The command should add a reminder.');
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
