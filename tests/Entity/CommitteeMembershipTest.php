<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\App\AbstractKernelTestCase;

class CommitteeMembershipTest extends AbstractKernelTestCase
{
    public const ADHERENT_UUID = '0f5afdb8-09f6-4522-9e36-f0fd227a8442';
    public const COMMITTEE_UUID = 'ebd9f0c8-4158-4939-8372-28505f6cf892';

    public function testCreateHostMembership()
    {
        $membership = CommitteeMembership::createForHost($this->createCommittee(), $adherent = $this->createNewAdherent(), new \DateTime());

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertInstanceOf(UuidInterface::class, $membership->getUuid());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame(self::ADHERENT_UUID, (string) $membership->getAdherentUuid());
        $this->assertSame(self::COMMITTEE_UUID, $membership->getCommitteeUuid()->toString());
        $this->assertFalse($membership->isSupervisor());
        $this->assertTrue($membership->isHostMember());
        $this->assertFalse($membership->isFollower());
    }

    public function testCreateFollowerMembership()
    {
        $membership = CommitteeMembership::createForAdherent($this->createCommittee(), $adherent = $this->createNewAdherent(), CommitteeMembership::COMMITTEE_FOLLOWER, new \DateTime());

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertInstanceOf(UuidInterface::class, $membership->getUuid());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame(self::ADHERENT_UUID, (string) $membership->getAdherentUuid());
        $this->assertSame(self::COMMITTEE_UUID, $membership->getCommitteeUuid()->toString());
        $this->assertFalse($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertTrue($membership->isFollower());
    }

    public function testChangePrivileges()
    {
        $membership = CommitteeMembership::createForHost($this->createCommittee(), $this->createNewAdherent(), new \DateTime());

        $this->assertFalse($membership->isSupervisor());
        $this->assertTrue($membership->isHostMember());
        $this->assertFalse($membership->isFollower());

        $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);

        $this->assertFalse($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertTrue($membership->isFollower());
    }

    private function createNewAdherent(): Adherent
    {
        return Adherent::create(
            Uuid::fromString(self::ADHERENT_UUID),
            'ABC-234',
            'foo@bar.com',
            'password',
            'male',
            'Jean',
            'Dupont',
            new \DateTime('1980-03-22'),
            'position',
            $this->createPostAddress('50 Rue de la Villette', '69003-69383')
        );
    }

    private function createCommittee(): Committee
    {
        return $this->createConfiguredMock(Committee::class, [
            'getUuid' => Uuid::fromString(self::COMMITTEE_UUID),
        ]);
    }
}
