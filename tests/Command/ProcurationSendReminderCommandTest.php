<?php

namespace Tests\App\Command;

use App\Mailer\Message\Procuration\ProcurationProxyReminderMessage;
use Tests\App\AbstractCommandCaseTest;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends AbstractCommandCaseTest
{
    use ControllerTestTrait;

    public function testCommand()
    {
        $output = $this->runCommand('app:procuration:send-reminder', ['processed-after' => (new \DateTime('-3 months'))->format('Y-m-d')]);
        $output = $output->getDisplay();
        $this->assertStringContainsString('3 reminders sent', $output);
        $this->assertCountMails(3, ProcurationProxyReminderMessage::class);
        $this->assertCount(3, $this->getProcurationRequestRepository()->findBy(['reminded' => 1]), 'The command should add a reminder.');
    }
}
