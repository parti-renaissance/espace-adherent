<?php

declare(strict_types=1);

namespace Tests\App\Committee;

use App\Committee\CommitteeMembershipManager;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('committeeMembershipManager')]
class CommitteeMembershipManagerInsertIfNotExistsTest extends AbstractKernelTestCase
{
    use ControllerTestTrait;

    private ?CommitteeMembershipManager $committeeMembershipManager;

    public function testFollowCommitteeIsIdempotentWhenMembershipAlreadyExists(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadCommitteeV1Data::COMMITTEE_1_UUID);

        // First call: a fresh row is inserted.
        $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::COMMITTEE_EDITION);

        self::assertNotNull($adherent->getCommitteeMembership());
        $firstMembershipId = $adherent->getCommitteeMembership()->getId();

        // Mirror the production bug: in-memory adherent has lost its membership
        // reference (what PARTIAL hydration produces on retry).
        $adherent->setCommitteeMembership(null);

        // Second call for the same pair: must NOT throw UniqueConstraintViolationException
        // and must NOT create a duplicate row.
        $this->committeeMembershipManager->followCommittee($adherent, $committee, CommitteeMembershipTriggerEnum::COMMITTEE_EDITION);

        $rows = $this->getEntityManager(\App\Entity\CommitteeMembership::class)
            ->getConnection()
            ->fetchAllAssociative(
                'SELECT id FROM committees_memberships WHERE adherent_id = :a AND committee_id = :c',
                ['a' => $adherent->getId(), 'c' => $committee->getId()],
            )
        ;
        self::assertCount(1, $rows);
        self::assertSame($firstMembershipId, (int) $rows[0]['id']);
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
