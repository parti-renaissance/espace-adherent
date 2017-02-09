<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Repository\CommitteeRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class CommitteeRepositoryTest extends SqliteWebTestCase
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
        $this->assertSame(7, $this->repository->countApprovedCommittees());
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->getAdherentRepository()->loadUserByUsername('referent@en-marche-dev.fr');
        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(3, $managedByReferent, 'Referent should manage 3 adherents in his area.');
        $this->assertSame('En Marche - Clichy', $managedByReferent[0]->getName());
        $this->assertSame('En Marche - Comité de Melun', $managedByReferent[1]->getName());
        $this->assertSame('En Marche Suisse', $managedByReferent[2]->getName());
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
