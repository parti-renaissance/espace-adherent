<?php

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class CommitteeMembershipRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$container = $this->getContainer();
        $this->repository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->repository = null;
        static::$container = null;

        parent::tearDown();
    }
}
