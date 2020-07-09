<?php

namespace Tests\App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event;
use App\Event\EventPermissions;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\HostEventVoter;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HostEventVoterTest extends AbstractAdherentVoterTest
{
    protected function getVoter(): AbstractAdherentVoter
    {
        return new HostEventVoter($this->createMock(SessionInterface::class));
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, EventPermissions::HOST, $this->createMock(Event::class)];
    }

    public function testAdherentIsGrantedIfIsOrganizer()
    {
        $adherent = $this->getAdherentMock(true);
        $event = $this->getEventMock($adherent);

        $this->assertGrantedForAdherent(true, true, $adherent, EventPermissions::HOST, $event);
    }

    public function testAdherentIsNotGrantedIfNotCommitteeEvent()
    {
        $adherent = $this->getAdherentMock();
        $event = $this->getEventMock(null, false);

        $this->assertGrantedForAdherent(false, true, $adherent, EventPermissions::HOST, $event);
    }

    public function testAdherentIsGranted()
    {
        $committee = $this->getCommitteeMock();
        $adherent = $this->getAdherentMock(false, true, $committee);
        $event = $this->getEventMock(null, true, $committee);

        $this->assertGrantedForAdherent(true, true, $adherent, EventPermissions::HOST, $event);
    }

    public function testAdherentIsNotGranted()
    {
        $committee = $this->getCommitteeMock();
        $adherent = $this->getAdherentMock(false, false, $committee);
        $event = $this->getEventMock(null, true, $committee);

        $this->assertGrantedForAdherent(false, true, $adherent, EventPermissions::HOST, $event);
    }

    /**
     * @return Adherent|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getAdherentMock(
        bool $isOrganizer = false,
        bool $isHost = null,
        Committee $committee = null
    ): Adherent {
        $adherent = $this->createAdherentMock();

        if ($isOrganizer) {
            $adherent->expects($this->once())
                ->method('equals')
                ->with($adherent)
                ->willReturn(true)
            ;
        } else {
            $adherent->expects($this->never())
                ->method('equals')
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
     * @return Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEventMock(?Adherent $organizer, bool $committeeEvent = null, Committee $committee = null): Event
    {
        $event = $this->createMock(Event::class);

        $event->expects($organizer ? $this->exactly(2) : $this->once())
            ->method('getOrganizer')
            ->willReturn($organizer ? $organizer : null)
        ;

        if (null !== $committeeEvent) {
            $event->expects($this->once())
                ->method('getCommittee')
                ->willReturn($committee)
            ;
        } else {
            $event->expects($this->never())
                ->method('getCommittee')
            ;
        }

        return $event;
    }

    /**
     * @return Committee|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCommitteeMock(bool $uuidChecked = false): Committee
    {
        $committee = $this->createMock(Committee::class);

        if ($uuidChecked) {
            $uuid = $this->createMock(UuidInterface::class);
            $uuid->expects($this->once())
                 ->method('toString')
                 ->willReturn('test')
            ;
            $committee->expects($this->once())
                ->method('getUuid')
                ->willReturn($uuid)
            ;
        } else {
            $committee->expects($this->never())
              ->method('getUuid')
            ;
        }

        return $committee;
    }
}
