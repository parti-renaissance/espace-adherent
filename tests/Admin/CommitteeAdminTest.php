<?php

namespace Tests\AppBundle\Admin;

use AppBundle\AdherentMessage\Command\CreateStaticSegmentCommand;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MessengerTestTrait;

/**
 * @group functional
 * @group admin
 */
class CommitteeAdminTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var CommitteeRepository
     */
    private $committeeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->committeeRepository = null;

        parent::tearDown();
    }

    public function testCommitteeStaticSegmentCommandIsDispatchedWhenCommitteeIsApproved(): void
    {
        $this->authenticateAsAdmin($this->client);

        $this->client->enableProfiler();
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $this->client->request('GET', sprintf('/admin/committee/%d/approve', $committee->getId()));

        $this->assertClientIsRedirectedTo('/admin/app/committee/list', $this->client);

        $this->assertMessageIsDispatched(CreateStaticSegmentCommand::class);
    }
}
