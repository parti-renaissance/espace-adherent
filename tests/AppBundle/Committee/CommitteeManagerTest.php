<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Geocoder\Coordinates;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeManagerTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeManager */
    private $committeeManager;

    public function testGetMembersCount()
    {
        $this->assertSame(4, $this->committeeManager->getMembersCount($this->getCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertSame(1, $this->committeeManager->getMembersCount($this->getCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
        $this->assertSame(1, $this->committeeManager->getMembersCount($this->getCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->committeeManager->getMembersCount($this->getCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID)));
        $this->assertSame(1, $this->committeeManager->getMembersCount($this->getCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID)));
    }

    public function testGetCommitteeHosts()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $hosts = $this->committeeManager->getCommitteeHosts($this->getCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID))
        );
        // Approved committees
        $this->assertCount(2, $hosts);
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID)));

        // Unapproved committees
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
    }

    public function testGetCommitteeFollowers()
    {
        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(
            AdherentCollection::class,
            $hosts = $this->committeeManager->getCommitteeFollowers($committee)
        );
        // Approved committees
        $this->assertCount(4, $hosts);
        $this->assertCount(2, $this->committeeManager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID);
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($committee));
        $this->assertCount(0, $this->committeeManager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID);
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($committee));
        $this->assertCount(0, $this->committeeManager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        // Unapproved committees
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($this->getCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($this->getCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
    }

    public function testGetOptinCommitteeFollowers()
    {
        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(
            AdherentCollection::class,
            $hosts = $this->committeeManager->getOptinCommitteeFollowers($committee)
        );
        // Approved committees
        $this->assertCount(3, $hosts);
        $this->assertCount(1, $this->committeeManager->getOptinCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID);
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($committee));
        $this->assertCount(0, $this->committeeManager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        $committee = $this->getCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID);
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($committee));
        $this->assertCount(0, $this->committeeManager->getCommitteeFollowers($committee, CommitteeManager::EXCLUDE_HOSTS));

        // Unapproved committees
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($this->getCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeFollowers($this->getCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
    }

    public function testGetNearbyCommittees()
    {
        $adherentRepository = $this->getAdherentRepository();
        $adherent = $adherentRepository->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->assertSame([
            LoadAdherentData::COMMITTEE_4_UUID,
            LoadAdherentData::COMMITTEE_3_UUID,
            LoadAdherentData::COMMITTEE_5_UUID,
        ], array_keys($this->committeeManager->getNearbyCommittees($coordinates)));

        $adherent = $adherentRepository->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->assertSame([
            LoadAdherentData::COMMITTEE_1_UUID,
            LoadAdherentData::COMMITTEE_5_UUID,
            LoadAdherentData::COMMITTEE_3_UUID,
        ], array_keys($this->committeeManager->getNearbyCommittees($coordinates)));
    }

    public function testFollowCommittees()
    {
        $adherentRepository = $this->getAdherentRepository();
        $adherent = $adherentRepository->findByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->assertCount(0, $this->getCommitteeMembershipRepository()->findMemberships($adherent));

        $committees = [
            LoadAdherentData::COMMITTEE_1_UUID,
            LoadAdherentData::COMMITTEE_3_UUID,
            LoadAdherentData::COMMITTEE_4_UUID,
        ];

        $this->committeeManager->followCommittees($adherent, $committees);

        $this->assertCount(3, $memberships = $this->getCommitteeMembershipRepository()->findMemberships($adherent));

        foreach ($memberships as $i => $membership) {
            /* @var CommitteeMembership $membership */
            $this->assertSame($committees[$i], $membership->getCommitteeUuid()->toString());
        }
    }

    private function getCommitteeMock(string $uuid)
    {
        $mock = $this
            ->getMockBuilder(Committee::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getUuid')
            ->willReturn(Uuid::fromString($uuid))
        ;

        return $mock;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->committeeManager = new CommitteeManager($this->getManagerRegistry());
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->committeeManager = null;

        parent::tearDown();
    }
}
