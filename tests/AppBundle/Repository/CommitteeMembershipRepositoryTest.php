<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeMembershipRepository;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class CommitteeMembershipRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testFindCommitteeHostMembersList()
    {
        // Approved committees
        $this->assertCount(2, $this->repository->findHostMembers(LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertCount(1, $this->repository->findHostMembers(LoadAdherentData::COMMITTEE_3_UUID));
        $this->assertCount(1, $this->repository->findHostMembers(LoadAdherentData::COMMITTEE_4_UUID));
        $this->assertCount(1, $this->repository->findHostMembers(LoadAdherentData::COMMITTEE_5_UUID));

        // Unapproved committees
        $this->assertCount(1, $this->repository->findHostMembers(LoadAdherentData::COMMITTEE_2_UUID));
    }

    public function testCountHostMembersInCommittee()
    {
        $this->assertSame(2, $this->repository->countHostMembers(LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testAdherentIsMemberOfCommittee()
    {
        $this->assertFalse($this->repository->isMemberOf($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testMemberIsCommitteeHost()
    {
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID)));
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), LoadAdherentData::COMMITTEE_2_UUID));

        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID)));
        $this->assertTrue($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), LoadAdherentData::COMMITTEE_2_UUID));

        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID)));
        $this->assertFalse($this->repository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID)));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}
