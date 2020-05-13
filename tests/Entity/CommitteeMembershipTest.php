<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\PostAddress;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CommitteeMembershipTest extends TestCase
{
    const ADHERENT_UUID = '0f5afdb8-09f6-4522-9e36-f0fd227a8442';
    const COMMITTEE_UUID = 'ebd9f0c8-4158-4939-8372-28505f6cf892';

    public function testGetHostPrivileges()
    {
        $privileges = CommitteeMembership::getHostPrivileges();

        $this->assertCount(2, $privileges);
        $this->assertContains(CommitteeMembership::COMMITTEE_SUPERVISOR, $privileges);
        $this->assertContains(CommitteeMembership::COMMITTEE_HOST, $privileges);
    }

    public function testCreateSupervisorMembership()
    {
        $membership = CommitteeMembership::createForSupervisor($this->createCommittee(), $adherent = $this->createAdherent());

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertInstanceOf(UuidInterface::class, $membership->getUuid());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame(self::ADHERENT_UUID, (string) $membership->getAdherentUuid());
        $this->assertSame(self::COMMITTEE_UUID, $membership->getCommitteeUuid()->toString());
        $this->assertTrue($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertFalse($membership->isFollower());
        $this->assertTrue($membership->canHostCommittee());
    }

    public function testCreateHostMembership()
    {
        $membership = CommitteeMembership::createForHost($this->createCommittee(), $adherent = $this->createAdherent());

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertInstanceOf(UuidInterface::class, $membership->getUuid());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame(self::ADHERENT_UUID, (string) $membership->getAdherentUuid());
        $this->assertSame(self::COMMITTEE_UUID, $membership->getCommitteeUuid()->toString());
        $this->assertFalse($membership->isSupervisor());
        $this->assertTrue($membership->isHostMember());
        $this->assertFalse($membership->isFollower());
        $this->assertTrue($membership->canHostCommittee());
    }

    public function testCreateFollowerMembership()
    {
        $membership = CommitteeMembership::createForAdherent($this->createCommittee(), $adherent = $this->createAdherent());

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertInstanceOf(UuidInterface::class, $membership->getUuid());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame(self::ADHERENT_UUID, (string) $membership->getAdherentUuid());
        $this->assertSame(self::COMMITTEE_UUID, $membership->getCommitteeUuid()->toString());
        $this->assertFalse($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertTrue($membership->isFollower());
        $this->assertFalse($membership->canHostCommittee());
    }

    public function testChangePrivileges()
    {
        $membership = CommitteeMembership::createForSupervisor($this->createCommittee(), $this->createAdherent());

        $this->assertTrue($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertFalse($membership->isFollower());
        $this->assertTrue($membership->canHostCommittee());
        $this->assertFalse($membership->isPromotableHost());
        $this->assertFalse($membership->isDemotableHost());

        $membership->setPrivilege(CommitteeMembership::COMMITTEE_HOST);

        $this->assertFalse($membership->isSupervisor());
        $this->assertTrue($membership->isHostMember());
        $this->assertFalse($membership->isFollower());
        $this->assertTrue($membership->canHostCommittee());
        $this->assertFalse($membership->isPromotableHost());
        $this->assertTrue($membership->isDemotableHost());

        $membership->setPrivilege(CommitteeMembership::COMMITTEE_FOLLOWER);

        $this->assertFalse($membership->isSupervisor());
        $this->assertFalse($membership->isHostMember());
        $this->assertTrue($membership->isFollower());
        $this->assertFalse($membership->canHostCommittee());
        $this->assertTrue($membership->isPromotableHost());
        $this->assertFalse($membership->isDemotableHost());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Uuid::fromString(self::ADHERENT_UUID),
            'foo@bar.com',
            'password',
            'male',
            'Jean',
            'Dupont',
            new \DateTime('1980-03-22'),
            'position',
            PostAddress::createFrenchAddress('50 Rue de la Villette', '69003-69383')
        );
    }

    private function createCommittee(): Committee
    {
        return $this->createConfiguredMock(Committee::class, [
            'getUuid' => Uuid::fromString(self::COMMITTEE_UUID),
        ]);
    }
}
