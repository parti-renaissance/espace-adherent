<?php

namespace Tests\App\Command;

use App\Mailer\Message\Procuration\ProcurationProxyReminderMessage;
use App\Mailer\Message\Procuration\ProcurationRequestReminderMessage;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\AbstractCommandCaseTest;
use Tests\App\TestHelperTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends AbstractCommandCaseTest
{
    use TestHelperTrait;

    public function testRequestReminderCommand(): void
    {
        $tester = new CommandTester($this->application->find('app:procuration:send-reminder'));
        $tester->setInputs(['yes']);
        $tester->execute([
            'procuration-mode' => 1,
            'processed-after' => (new \DateTime('-3 months'))->format('Y-m-d'),
        ]);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Are you sure to send the reminder to 3 requests? (yes/no) [no]', $output);

        $this->assertStringContainsString('3 reminders sent', $output);
        $this->assertCountMails(1, ProcurationRequestReminderMessage::class);
        $this->assertCount(3, $this->getProcurationRequestRepository()->createQueryBuilder('pr')->where('pr.remindedAt IS NOT NULL')->getQuery()->getResult(), 'The command should add a reminder.');
    }

    public function testProxyReminderCommand(): void
    {
        $tester = new CommandTester($this->application->find('app:procuration:send-reminder'));
        $tester->setInputs(['yes']);
        $tester->execute([
            'procuration-mode' => 2,
            'processed-after' => (new \DateTime('-3 months'))->format('Y-m-d'),
        ]);
        $output = $tester->getDisplay();
        $this->assertStringContainsString('Are you sure to send the reminder to 2 proxies? (yes/no) [no]', $output);

        $this->assertStringContainsString('2 reminders sent', $output);
        $this->assertCountMails(1, ProcurationProxyReminderMessage::class);
        $this->assertCount(2, $this->getProcurationProxyRepository()->createQueryBuilder('pp')->where('pp.remindedAt IS NOT NULL')->getQuery()->getResult(), 'The command should add a reminder.');
    }
}
