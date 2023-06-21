<?php

namespace Tests\App\Security\Voter\Committee;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\Committee\SuperviseCommitteeVoter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\Security\Voter\AbstractAdherentVoterTestCase;

class SuperviseCommitteeVoterTest extends AbstractAdherentVoterTestCase
{
    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, CommitteePermissionEnum::SUPERVISE, fn (self $_this) => $_this->createMock(Committee::class)];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new SuperviseCommitteeVoter();
    }

    public function testSupervisorIsNotGrantedIfCommitteeIsBlocked()
    {
        $committee = $this->createCommitteeMock(true);
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->never())
            ->method('isSupervisorOf')
            ->with($committee)
        ;

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissionEnum::SUPERVISE, $committee);
    }

    public function testAdherentIsNotGranted()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock(false, $committee);

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissionEnum::SUPERVISE, $committee);
    }

    public function testSupervizorIsGranted()
    {
        $committee = $this->createCommitteeMock();
        $adherent = $this->getAdherentMock(true, $committee);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissionEnum::SUPERVISE, $committee);
    }

    /**
     * @return Adherent|MockObject
     */
    private function getAdherentMock(bool $isSupervisor, Committee $committee): Adherent
    {
        $adherent = $this->createAdherentMock();

        $adherent->expects($this->once())
            ->method('isSupervisorOf')
            ->with($committee)
            ->willReturn($isSupervisor)
        ;

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
