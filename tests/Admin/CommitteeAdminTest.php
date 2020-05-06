<?php

namespace Tests\App\Admin;

use App\AdherentMessage\Command\CreateStaticSegmentCommand;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

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

    public function testCommitteeStaticSegmentCommandIsDispatchedWhenCommitteeIsApproved(): void
    {
        $this->authenticateAsAdmin($this->client);

        $this->client->enableProfiler();
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_2_UUID);

        $this->client->request('GET', sprintf('/admin/committee/%d/approve', $committee->getId()));

        $this->assertClientIsRedirectedTo('/admin/app/committee/list', $this->client);

        $this->assertMessageIsDispatched(CreateStaticSegmentCommand::class);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->committeeRepository = $this->getCommitteeRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->committeeRepository = null;

        parent::tearDown();
    }
}
