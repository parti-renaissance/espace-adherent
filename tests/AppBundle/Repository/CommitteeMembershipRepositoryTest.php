<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\CommitteeMembershipRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

class CommitteeMembershipRepositoryTest extends WebTestCase
{
    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testCountHostMembersInCommittee()
    {
        $this->assertSame(2, $this->repository->countHostMembers(LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership(LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership(LoadAdherentData::ADHERENT_2_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership(LoadAdherentData::ADHERENT_3_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership(LoadAdherentData::ADHERENT_4_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership(LoadAdherentData::ADHERENT_5_UUID, LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testAdherentIsMemberOfCommittee()
    {
        $this->assertFalse($this->repository->isMemberOf(LoadAdherentData::ADHERENT_1_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf(LoadAdherentData::ADHERENT_2_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf(LoadAdherentData::ADHERENT_3_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf(LoadAdherentData::ADHERENT_4_UUID, LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertTrue($this->repository->isMemberOf(LoadAdherentData::ADHERENT_5_UUID, LoadAdherentData::COMMITTEE_1_UUID));
    }

    public function testMemberIsCommitteeHost()
    {
        $this->assertTrue($this->repository->isCommitteeHost(LoadAdherentData::ADHERENT_3_UUID));
        $this->assertTrue($this->repository->isCommitteeHost(LoadAdherentData::ADHERENT_5_UUID));
        $this->assertFalse($this->repository->isCommitteeHost(LoadAdherentData::ADHERENT_2_UUID));
        $this->assertFalse($this->repository->isCommitteeHost(LoadAdherentData::ADHERENT_4_UUID));
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
