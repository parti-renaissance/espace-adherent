<?php

namespace Tests\App\Committee;

use App\Collection\AdherentCollection;
use App\Committee\CommitteeManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\DataFixtures\ORM\LoadCommitteeV2Data;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\ReferentTag;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Exception\CommitteeMembershipException;
use App\Geocoder\Coordinates;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('committeeManager')]
class CommitteeManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeManager */
    private $committeeManager;

    public function testGetCommitteeHosts()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $hosts = $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID))
        );
        // Approved committees
        $this->assertCount(2, $hosts);
        $this->assertCount(4, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)));
        $this->assertCount(2, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_5_UUID)));

        // Unapproved committees
        $this->assertCount(0, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));
    }

    public function testGetOptinCommitteeFollowers()
    {
        $this->assertCount(2, $this->committeeManager->getOptinCommitteeFollowers($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertCount(1, $this->committeeManager->getOptinCommitteeFollowers($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertCount(10, $this->committeeManager->getOptinCommitteeFollowers($this->getCommittee(LoadCommitteeV2Data::COMMITTEE_1_UUID)));
        $this->assertCount(3, $this->committeeManager->getOptinCommitteeFollowers($this->getCommittee(LoadCommitteeV2Data::COMMITTEE_2_UUID)));
    }

    public function testGetNearbyCommittees()
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID);
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->assertSame([
            LoadCommitteeV1Data::COMMITTEE_10_UUID,
            LoadCommitteeV1Data::COMMITTEE_4_UUID,
            LoadCommitteeV1Data::COMMITTEE_3_UUID,
        ], array_keys($this->committeeManager->getNearbyCommittees($coordinates)));

        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_3_UUID);
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $this->assertSame([
            LoadCommitteeV1Data::COMMITTEE_1_UUID,
            LoadCommitteeV1Data::COMMITTEE_5_UUID,
            LoadCommitteeV1Data::COMMITTEE_3_UUID,
        ], array_keys($this->committeeManager->getNearbyCommittees($coordinates)));
    }

    public function testMembershipEventIsRecordedWhenFollowOrUnfollowCommittee(): void
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->committeeManager->followCommittee($adherent, $committee);

        $this->assertCount(1, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $membershipHistory = $this->getCommitteeMembershipHistoryRepository()->findOneBy(['adherentUuid' => $adherent->getUuid()]);

        /* @var CommitteeMembershipHistory $membershipHistory */
        $this->assertSame($committee, $membershipHistory->getCommittee());
        $this->assertSame(LoadAdherentData::ADHERENT_1_UUID, $membershipHistory->getAdherentUuid()->toString());
        $this->assertSame('FOLLOWER', $membershipHistory->getPrivilege());
        $this->assertSame('join', $membershipHistory->getAction());
        $this->assertEquals(['75008', '75'], $this->getReferentTagCodes($membershipHistory));

        $this->committeeManager->unfollowCommittee($adherent, $committee);

        $this->assertCount(0, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $membershipHistory = $this->getCommitteeMembershipHistoryRepository()->findOneBy(['adherentUuid' => $adherent->getUuid(), 'action' => 'leave']);

        /* @var CommitteeMembershipHistory $membershipHistory */
        $this->assertSame($committee, $membershipHistory->getCommittee());
        $this->assertSame(LoadAdherentData::ADHERENT_1_UUID, $membershipHistory->getAdherentUuid()->toString());
        $this->assertSame('FOLLOWER', $membershipHistory->getPrivilege());
        $this->assertSame('leave', $membershipHistory->getAction());
        $this->assertEquals(['75008', '75'], $this->getReferentTagCodes($membershipHistory));
    }

    private function getReferentTagCodes(CommitteeMembershipHistory $history): array
    {
        return array_map(
            function (ReferentTag $tag) { return $tag->getCode(); },
            $history->getReferentTags()->toArray()
        );
    }

    public function testFollowThenUnfollowCommittees(): void
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->assertCount(0, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $this->assertCount(0, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeManager->followCommittee($adherent, $committee);

        $this->assertCount(1, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $this->assertCount(1, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeManager->unfollowCommittee($adherent, $committee);

        $this->assertCount(0, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $this->assertCount(2, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeManager->followCommittee($adherent, $committee);

        $this->assertCount(1, $this->getCommitteeMembershipRepository()->findMemberships($adherent));
        $this->assertCount(3, $this->findCommitteeMembershipHistoryByAdherent($adherent));
    }

    public function testChangePrivilegeNotDefinedPrivilege()
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid privilege WRONG_PRIVILEGE');

        $this->committeeManager->changePrivilege($adherent, $committee, 'WRONG_PRIVILEGE');
    }

    public function testChangePrivilegeException()
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_3_UUID);
        $committee = $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->expectException(CommitteeMembershipException::class);
        $this->expectExceptionMessage(sprintf('Committee membership "%s" cannot be promoted to the host privilege', $adherent->getMembershipFor($committee)->getUuid()));

        $this->committeeManager->changePrivilege($adherent, $committee, CommitteeMembership::COMMITTEE_HOST);
    }

    public function testChangePrivilege()
    {
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_5_UUID);
        $adherent2 = $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID);
        $committee = $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        // Change privileges of the a member HOST => FOLLOWER => HOST
        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isHostMember());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isFollower());

        $this->committeeManager->changePrivilege($adherent, $committee, CommitteeMembership::COMMITTEE_FOLLOWER);

        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isFollower());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isHostMember());

        $this->committeeManager->changePrivilege($adherent, $committee, CommitteeMembership::COMMITTEE_HOST);

        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isHostMember());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isFollower());

        // Change privileges of another member: FOLLOWER => HOST
        $this->assertEquals(true, $adherent2->getMembershipFor($committee)->isFollower());

        $committee6 = $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_6_UUID);
        $adherent2->getMembershipFor($committee6)->removeCommitteeCandidacyForElection($committee6->getCommitteeElection());

        $this->committeeManager->changePrivilege($adherent2, $committee, CommitteeMembership::COMMITTEE_HOST);

        $this->assertEquals(false, $adherent2->getMembershipFor($committee)->isFollower());
        $this->assertEquals(true, $adherent2->getMembershipFor($committee)->isHostMember());
    }

    public function testApproveRefuseCommittee()
    {
        // Creator of committee
        $adherent = $this->getAdherent(LoadAdherentData::ADHERENT_6_UUID);
        $committee = $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID);

        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isFollower());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isSupervisor());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isHostMember());

        // Approve committee
        $this->committeeManager->approveCommittee($committee);

        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isSupervisor());
        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isFollower());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isHostMember());

        // Refuse approved committee
        $this->committeeManager->refuseCommittee($committee);

        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isFollower());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isSupervisor());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isHostMember());

        // Reapprove committee
        $this->committeeManager->approveCommittee($committee);

        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isSupervisor());
        $this->assertEquals(true, $adherent->getMembershipFor($committee)->isFollower());
        $this->assertEquals(false, $adherent->getMembershipFor($committee)->isHostMember());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeManager = new CommitteeManager(
            $this->getEntityManager(Committee::class),
            $this->get('event_dispatcher'),
            $this->get(CommitteeAdherentMandateRepository::class)
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->committeeManager = null;
    }

    private function findCommitteeMembershipHistoryByAdherent(Adherent $adherent): array
    {
        return $this->getCommitteeMembershipHistoryRepository()->findBy(
            ['adherentUuid' => $adherent->getUuid()]
        );
    }
}
