<?php

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class CommitteeMembershipRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var CommitteeMembershipRepository
     */
    private $repository;

    public function testFindCommitteeMembersMemberships()
    {
        $this->assertNull($this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertInstanceOf(CommitteeMembership::class, $this->repository->findMembership($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
