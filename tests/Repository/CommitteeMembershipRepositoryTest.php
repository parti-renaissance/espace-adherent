<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeMembershipRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CommitteeMembershipRepositoryTest extends WebTestCase
{
    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testFindCommitteeHostMembersList()
    {
        // Approved committees
        $this->assertCount(2, $this->repository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)), '1 supervisor + 1 host');
        $this->assertCount(2, $this->repository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_3_UUID)), '1 supervisor + 1 host');
        $this->assertCount(1, $this->repository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_4_UUID)), '1 supervisor');
        $this->assertCount(1, $this->repository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_5_UUID)), '1 supervisor');

        // Unapproved committees
        $this->assertCount(0, $this->repository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_2_UUID)));
    }

    public function testCountHostMembersInCommittee()
    {
        $this->assertSame(2, $this->repository->countHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertSame(2, $this->repository->countHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->repository->countHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_4_UUID)));
    }

    public function testCountSupervisorMembersInCommittee()
    {
        $this->assertSame(1, $this->repository->countSupervisorMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertSame(1, $this->repository->countSupervisorMembers($this->getCommittee(LoadAdherentData::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->repository->countSupervisorMembers($this->getCommittee(LoadAdherentData::COMMITTEE_7_UUID)));
    }

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
    }

    public function testMemberIsCommitteeHost()
    {
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID)));
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_2_UUID)));

        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID)));
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadAdherentData::COMMITTEE_2_UUID)));

        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID)));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getContainer();
        $this->repository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
