<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\ProcurationSendReminderCommand;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Mailer\Message\ProcurationProxyReminderMessage;
use AppBundle\Repository\ProcurationRequestRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepository;

    public function testCommand()
    {
        $this->decorated = false;
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);

        $this->assertContains('1 reminders sent', $output);
        $this->assertCountMails(1, ProcurationProxyReminderMessage::class);
        $this->assertCount(1, $this->procurationRequestRepository->findBy(['reminded' => 1]), 'The command should add a reminder.');
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadProcurationData::class,
        ]);

        $this->container = $this->getContainer();
        $this->procurationRequestRepository = $this->getProcurationRequestRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        $this->procurationRequestRepository = null;

        parent::tearDown();
    }
}
