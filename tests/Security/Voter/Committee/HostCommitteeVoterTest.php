<?php

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\HostCommitteeVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTest;

class HostCommitteeVoterTest extends AbstractAdherentVoterTest
{
    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissions::HOST, $this->createMock(Committee::class)];
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

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCanHostCommitteeIfSupervisor()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCanHostCommitteeIfHost()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, false, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCannotHostCommitteeIfNotHostAndNotSupervisor()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock($committee, false, false);

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(Committee $committee, bool $isSupervisor, bool $isHost = null): Adherent
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
