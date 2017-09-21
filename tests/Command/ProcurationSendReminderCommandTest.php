<?php

namespace AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadProcurationData;
use AppBundle\Mailer\Message\ProcurationProxyReminderMessage;
use AppBundle\Repository\ProcurationRequestRepository;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class ProcurationSendReminderCommandTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /** @var ProcurationRequestRepository */
    private $procurationRequestRepository;

    public function testCommand()
    {
        $this->decorated = false;
        $output = $this->runCommand(ProcurationSendReminderCommand::COMMAND_NAME);

        $this->assertContains('1 reminders sent', $output);
        $this->assertCountMails(1, ProcurationProxyReminderMessage::class);
        $this->assertCount(1, $this->procurationRequestRepository->findBy(['reminded' => 1]));
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
        $this->loadFixtures([]);

        $this->container = null;
        $this->procurationRequestRepository = null;

        parent::tearDown();
    }
}
