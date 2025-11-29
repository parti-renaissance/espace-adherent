<?php

declare(strict_types=1);

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\HostCommitteeVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTestCase;

class HostCommitteeVoterTest extends AbstractAdherentVoterTestCase
{
    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissionEnum::HOST, fn (self $_this) => $_this->createMock(Committee::class)];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new HostCommitteeVoter();
    }

    public function testSupervisorIsNotGrantedIfCommitteeIsBlocked()
    {
        $committee = $this->createCommitteeMock(true);
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->never())
            ->method('isSupervisorOf')
            ->with($committee)
        ;
        $adherent->expects($this->never())
            ->method('isHostOf')
            ->with($committee)
        ;

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissionEnum::HOST, $committee);
    }

    public function testAdherentCanHostCommitteeIfSupervisor()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissionEnum::HOST, $committee);
    }

    public function testAdherentCanHostCommitteeIfHost()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, false, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissionEnum::HOST, $committee);
    }

    public function testAdherentCannotHostCommitteeIfNotHostAndNotSupervisor()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, false, false);

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissionEnum::HOST, $committee);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(Committee $committee, bool $isSupervisor, ?bool $isHost = null): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isSupervisorOf')
            ->with($committee)
            ->willReturn($isSupervisor)
        ;

        if (null !== $isHost) {
            $adherent->expects($this->once())
                ->method('isHostOf')
                ->with($committee)
                ->willReturn($isHost)
            ;
        }

        return $adherent;
    }

    /**
     * @return Committee|MockObject
     */
    private function createCommitteeMock(bool $isBlocked = false): Committee
    {
        $committee = $this->createMock(Committee::class);
        $committee->expects($this->once())
            ->method('isBlocked')
            ->willReturn($isBlocked)
        ;

        return $committee;
    }
}
