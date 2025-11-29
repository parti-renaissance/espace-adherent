<?php

declare(strict_types=1);

namespace Tests\App\Committee;

use App\Collection\AdherentCollection;
use App\Committee\CommitteeManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Geo\ZoneMatcher;
use App\Repository\CommitteeRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('committeeManager')]
class CommitteeManagerTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    private ?CommitteeManager $committeeManager;
    private ?CommitteeMembershipManager $committeeMembershipManager;

    public function testGetCommitteeHosts()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $hosts = $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID))
        );
        // Approved committees
        $this->assertCount(1, $hosts);
        $this->assertCount(4, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertCount(1, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)));
        $this->assertCount(2, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_5_UUID)));

        // Unapproved committees
        $this->assertCount(0, $this->committeeManager->getCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));
    }

    public function testMembershipEventIsRecordedWhenFollowOrUnfollowCommittee(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->assertNotNull($adherent->getCommitteeMembership());
        $membershipHistory = $this->getCommitteeMembershipHistoryRepository()->findOneBy(['adherentUuid' => $adherent->getUuid()]);

        /* @var CommitteeMembershipHistory $membershipHistory */
        $this->assertSame($committee, $membershipHistory->getCommittee());
        $this->assertSame(LoadAdherentData::ADHERENT_1_UUID, $membershipHistory->getAdherentUuid()->toString());
        $this->assertSame('FOLLOWER', $membershipHistory->getPrivilege());
        $this->assertSame('join', $membershipHistory->getAction());

        $this->committeeMembershipManager->unfollowCommittee($adherent->getCommitteeMembership());

        $this->assertNull($adherent->getCommitteeMembership());
        $membershipHistory = $this->getCommitteeMembershipHistoryRepository()->findOneBy(['adherentUuid' => $adherent->getUuid(), 'action' => 'leave']);

        /* @var CommitteeMembershipHistory $membershipHistory */
        $this->assertSame($committee, $membershipHistory->getCommittee());
        $this->assertSame(LoadAdherentData::ADHERENT_1_UUID, $membershipHistory->getAdherentUuid()->toString());
        $this->assertSame('FOLLOWER', $membershipHistory->getPrivilege());
        $this->assertSame('leave', $membershipHistory->getAction());
    }

    public function testFollowThenUnfollowCommittees(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        $this->assertNull($adherent->getCommitteeMembership());
        $this->assertCount(0, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->assertNotNull($adherent->getCommitteeMembership());
        $this->assertCount(1, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeMembershipManager->unfollowCommittee($adherent->getCommitteeMembership());

        $this->assertNull($adherent->getCommitteeMembership());
        $this->assertCount(2, $this->findCommitteeMembershipHistoryByAdherent($adherent));

        $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::MANUAL);

        $this->assertNotNull($adherent->getCommitteeMembership());
        $this->assertCount(3, $this->findCommitteeMembershipHistoryByAdherent($adherent));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeManager = new CommitteeManager(
            $this->getEntityManager(Committee::class),
            $this->get(CommitteeMembershipManager::class),
            $this->get(CommitteeRepository::class),
            $this->get(EventDispatcherInterface::class),
            $this->get(ZoneMatcher::class),
        );
        $this->committeeMembershipManager = $this->get(CommitteeMembershipManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->committeeManager = null;
        $this->committeeMembershipManager = null;
    }

    private function findCommitteeMembershipHistoryByAdherent(Adherent $adherent): array
    {
        return $this->getCommitteeMembershipHistoryRepository()->findBy(
            ['adherentUuid' => $adherent->getUuid()]
        );
    }
}
