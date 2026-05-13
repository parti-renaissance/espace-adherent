<?php

declare(strict_types=1);

namespace Tests\App\Committee;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\Entity\CommitteeMembership;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('committeeMembershipManager')]
class CommitteeMembershipManagerBulkTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    private ?CommitteeMembershipManager $committeeMembershipManager;

    public function testFollowCommitteesBulkInsertsMovesAndIsIdempotent(): void
    {
        // Setup: adherent_1 has no membership, adherent_3 is in COMMITTEE_1 via fixtures.
        $adherent1 = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $adherent3 = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $committee2 = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_2_UUID);

        $connection = $this->getEntityManager(CommitteeMembership::class)->getConnection();

        // Sanity check fixture state.
        self::assertSame(
            0,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM committees_memberships WHERE adherent_id = ?', [$adherent1->getId()]),
        );
        self::assertSame(
            1,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM committees_memberships WHERE adherent_id = ?', [$adherent3->getId()]),
        );

        // First call: both adherents land in committee_2 (1 insert + 1 move).
        $created = $this->committeeMembershipManager->followCommitteesBulk(
            $committee2,
            [$adherent1, $adherent3],
            CommitteeMembershipTriggerEnum::COMMITTEE_EDITION,
        );

        self::assertSame(2, $created->newMembershipCount);
        self::assertCount(2, $created->newMemberships);
        self::assertCount(1, $created->removedMemberships);
        self::assertSame($committee2->getId(), $created->newMemberships[0]['committeeId']);
        self::assertSame($adherent1->getUuid()->toString(), $created->newMemberships[0]['uuid']->toString());
        self::assertSame($adherent3->getUuid()->toString(), $created->newMemberships[1]['uuid']->toString());
        self::assertNotSame($committee2->getId(), $created->removedMemberships[0]['committeeId']);
        self::assertSame($adherent3->getUuid()->toString(), $created->removedMemberships[0]['uuid']->toString());

        // Each adherent now has exactly one membership, in committee_2.
        foreach ([$adherent1, $adherent3] as $adherent) {
            $rows = $connection->fetchAllAssociative(
                'SELECT committee_id FROM committees_memberships WHERE adherent_id = ?',
                [$adherent->getId()],
            );
            self::assertCount(1, $rows);
            self::assertSame($committee2->getId(), (int) $rows[0]['committee_id']);
        }

        // History rows were written: adherent_1 → 1 JOIN ; adherent_3 → 1 LEAVE + 1 JOIN.
        $adh1JoinCount = (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM committees_membership_histories WHERE adherent_uuid = ? AND action = 'join' AND committee_id = ?",
            [$adherent1->getUuid()->toString(), $committee2->getId()],
        );
        self::assertSame(1, $adh1JoinCount);

        $adh3JoinCount = (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM committees_membership_histories WHERE adherent_uuid = ? AND action = 'join' AND committee_id = ?",
            [$adherent3->getUuid()->toString(), $committee2->getId()],
        );
        self::assertSame(1, $adh3JoinCount);

        $adh3LeaveCount = (int) $connection->fetchOne(
            "SELECT COUNT(*) FROM committees_membership_histories WHERE adherent_uuid = ? AND action = 'leave'",
            [$adherent3->getUuid()->toString()],
        );
        self::assertGreaterThanOrEqual(1, $adh3LeaveCount);

        // Second call with the same input: no-op (both adherents already in target).
        $created2 = $this->committeeMembershipManager->followCommitteesBulk(
            $committee2,
            [$adherent1, $adherent3],
            CommitteeMembershipTriggerEnum::COMMITTEE_EDITION,
        );

        self::assertSame(0, $created2->newMembershipCount);
        self::assertSame([], $created2->newMemberships);
        self::assertSame([], $created2->removedMemberships);

        // No duplicates.
        self::assertSame(
            1,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM committees_memberships WHERE adherent_id = ?', [$adherent1->getId()]),
        );
        self::assertSame(
            1,
            (int) $connection->fetchOne('SELECT COUNT(*) FROM committees_memberships WHERE adherent_id = ?', [$adherent3->getId()]),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeMembershipManager = $this->get(CommitteeMembershipManager::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->committeeMembershipManager = null;
    }
}
