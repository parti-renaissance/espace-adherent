<?php

namespace Tests\AppBundle\Command;

use AppBundle\Command\ProcurationSendReminderCommand;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Mail\Transactional\ProcurationProxyReminderMail;
use AppBundle\Repository\ProcurationRequestRepository;
use EnMarche\MailerBundle\Test\MailTestCaseTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group procuration
 */
class ProcurationSendReminderCommandTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailTestCaseTrait;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepository;

    public function testCommand()
    {
        $this->decorated = false;
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);

        $this->assertContains('1 reminders sent', $output);
        $this->assertMailCountForClass(1, ProcurationProxyReminderMail::class);
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
        $this->clearMails();
        parent::tearDown();
    }
}
