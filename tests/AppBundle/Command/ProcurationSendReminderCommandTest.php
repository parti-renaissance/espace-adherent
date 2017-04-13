<?php

namespace AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class ProcurationSendReminderCommandTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /* @var MailjetEmailRepository */
    private $mailjetEmailRepository;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepository;

    /**
     * @group functionnal
     */
    public function testCommand()
    {
        $this->decorated = false;
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);

        $this->assertContains('1 reminders sent', $output);
        $this->assertCount(1, $this->mailjetEmailRepository->findByMessageClass('ProcurationProxyReminderMessage'));
        $this->assertCount(1, $this->procurationRequestRepository->findByReminded(1));
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadProcurationData::class,
        ]);

        $this->container = $this->getContainer();
        $this->mailjetEmailRepository = $this->getMailjetEmailRepository();
        $this->procurationRequestRepository = $this->getProcurationRequestRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->mailjetEmailRepository = null;
        $this->procurationRequestRepository = null;

        parent::tearDown();
    }
}
