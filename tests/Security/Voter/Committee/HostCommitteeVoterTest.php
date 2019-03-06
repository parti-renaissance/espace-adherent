<?php

namespace Tests\AppBundle\Security\Voter\Committee;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\Committee\HostCommitteeVoter;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\UuidInterface;
use Tests\AppBundle\Security\Voter\AbstractAdherentVoterTest;

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

    public function testAdherentCannotHostNotApprovedCommittee()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock(false, $committee);

        // Hosts cannot either
        $adherent->expects($this->never())
            ->method('isHostOf')
        ;

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCanHostNotApprovedCommitteeIfSupervisor()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock(true, $committee);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCanHostNotApprovedCommitteeIfCreator()
    {
        $committee = $this->getCommitteeMock(false);
        $adherent = $this->getAdherentMock(false, $committee, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testAdherentCannotHostApprovedCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock(null, $committee, false, false);

        $this->assertGrantedForAdherent(false, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    public function testHostIsGrantedForApprovedCommittee()
    {
        $committee = $this->getCommitteeMock(true);
        $adherent = $this->getAdherentMock(null, $committee, false, true);

        $this->assertGrantedForAdherent(true, true, $adherent, CommitteePermissions::HOST, $committee);
    }

    /**
     * @param bool                                                    $isSupervisor
     * @param Committee|\PHPUnit_Framework_MockObject_MockObject|null $committee
     * @param bool|null                                               $isCreator
     *
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        bool $isSupervisor = null,
        Committee $committee = null,
        bool $isCreator = false,
        bool $isHost = null
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if (null !== $isSupervisor) {
            $adherent->expects($this->once())
                ->method('isSupervisorOf')
                ->with($committee)
                ->willReturn($isSupervisor)
            ;
            if (!$isSupervisor) {
                $adherent->expects($this->once())
                    ->method('getUuid')
                    ->willReturn($uuid = $this->createMock(UuidInterface::class))
                ;
                $committee->expects($this->once())
                    ->method('isCreatedBy')
                    ->with($uuid)
                    ->willReturn($isCreator)
                ;
            }
        } else {
            $adherent->expects($this->never())
                ->method('isSupervisorOf')
            ;
        }

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
     * @return Committee|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCommitteeMock(bool $approved): Committee
    {
        $committee = $this->createMock(Committee::class);

        $committee->expects($this->once())
            ->method('isApproved')
            ->willReturn($approved)
        ;

        return $committee;
    }
}
