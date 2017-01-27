<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

class CommitteeRepositoryTest extends WebTestCase
{
    /**
     * @var CommitteeRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testAdherentHasWaitingForApprovalCommittees()
    {
        $this->assertFalse($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_1_UUID));
        $this->assertFalse($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_2_UUID));
        $this->assertFalse($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_3_UUID));
        $this->assertFalse($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_4_UUID));
        $this->assertFalse($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_5_UUID));
        $this->assertTrue($this->repository->hasWaitingForApprovalCommittees(LoadAdherentData::ADHERENT_6_UUID));
    }

    public function testCountApprovedCommittees()
    {
        $this->assertSame(3, $this->repository->countApprovedCommittees());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getCommitteeRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
