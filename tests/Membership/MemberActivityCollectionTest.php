<?php

namespace Tests\App\Membership;

use App\Entity\Adherent;
use App\Entity\BaseEvent;
use App\Entity\EventRegistration;
use App\Membership\MemberActivityCollection;
use PHPUnit\Framework\TestCase;

/**
 * @group membership
 */
class MemberActivityCollectionTest extends TestCase
{
    public function testGetLogsWithRegisteredAt()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(new \DateTime('2017-08-06')), []);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(1, $logsToArray);
        $this->assertSame(0, $collection->getEventParticipationsCount());

        $this->assertSame('A rejoint le mouvement En Marche (06/08/2017)', $logsToArray[0]);
    }

    public function testGetLogsWithoutRegisteredAt()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(), []);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(0, $logsToArray);
        $this->assertSame(0, $collection->getEventParticipationsCount());
    }

    public function testGetLogsWithEventRegistrations()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(), [
            $this->getEventRegistrationMock($this->getEventMock('Réunion de réflexion parisienne'), new \DateTimeImmutable('2017-08-11')),
            $this->getEventRegistrationMock($this->getEventMock('Nettoyage de la Kilchberg'), new \DateTimeImmutable('2017-08-01')),
            $this->getEventRegistrationMock($this->getEventMock('Meeting de Brooklyn'), new \DateTimeImmutable('2017-08-06')),
        ]);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(3, $logsToArray);
        $this->assertSame(3, $collection->getEventParticipationsCount());

        $this->assertSame('A participé à l\'événement "Réunion de réflexion parisienne" (11/08/2017)', $logsToArray[0]);
        $this->assertSame('A participé à l\'événement "Meeting de Brooklyn" (06/08/2017)', $logsToArray[1]);
        $this->assertSame('A participé à l\'événement "Nettoyage de la Kilchberg" (01/08/2017)', $logsToArray[2]);
    }

    public function testGetLogsWithEvents()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(), [], [
            $this->getEventMock('Réunion de réflexion parisienne', new \DateTime('2017-08-10')),
            $this->getEventMock('Nettoyage de la Kilchberg', new \DateTime('2017-07-17')),
            $this->getEventMock('Meeting de Brooklyn', new \DateTime('2017-08-01')),
        ]);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(3, $logsToArray);
        $this->assertSame(0, $collection->getEventParticipationsCount());

        $this->assertSame('A créé l\'événement "Réunion de réflexion parisienne" (10/08/2017)', $logsToArray[0]);
        $this->assertSame('A créé l\'événement "Meeting de Brooklyn" (01/08/2017)', $logsToArray[1]);
        $this->assertSame('A créé l\'événement "Nettoyage de la Kilchberg" (17/07/2017)', $logsToArray[2]);
    }

    public function testGetLogsWithEventRegistrationsAndEvents()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(), [
            $this->getEventRegistrationMock($this->getEventMock('Réunion de réflexion parisienne'), new \DateTimeImmutable('2017-08-11')),
            $this->getEventRegistrationMock($this->getEventMock('Nettoyage de la Kilchberg'), new \DateTimeImmutable('2017-08-02')),
            $this->getEventRegistrationMock($this->getEventMock('Meeting de Brooklyn'), new \DateTimeImmutable('2017-08-06')),
        ], [
            $this->getEventMock('Réunion de réflexion parisienne', new \DateTime('2017-08-10')),
            $this->getEventMock('Nettoyage de la Kilchberg', new \DateTime('2017-07-17')),
            $this->getEventMock('Meeting de Brooklyn', new \DateTime('2017-08-01')),
        ]);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(6, $logsToArray);
        $this->assertSame(3, $collection->getEventParticipationsCount());

        $this->assertSame('A participé à l\'événement "Réunion de réflexion parisienne" (11/08/2017)', $logsToArray[0]);
        $this->assertSame('A créé l\'événement "Réunion de réflexion parisienne" (10/08/2017)', $logsToArray[1]);
        $this->assertSame('A participé à l\'événement "Meeting de Brooklyn" (06/08/2017)', $logsToArray[2]);
        $this->assertSame('A participé à l\'événement "Nettoyage de la Kilchberg" (02/08/2017)', $logsToArray[3]);
        $this->assertSame('A créé l\'événement "Meeting de Brooklyn" (01/08/2017)', $logsToArray[4]);
        $this->assertSame('A créé l\'événement "Nettoyage de la Kilchberg" (17/07/2017)', $logsToArray[5]);
    }

    public function testGetLogsWithRegisteredAtAndEventRegistrationsAndEvents()
    {
        $collection = new MemberActivityCollection($this->getAdherentMock(new \DateTime('2017-08-06')), [
            $this->getEventRegistrationMock($this->getEventMock('Réunion de réflexion parisienne'), new \DateTimeImmutable('2017-08-11')),
            $this->getEventRegistrationMock($this->getEventMock('Nettoyage de la Kilchberg'), new \DateTimeImmutable('2017-08-02')),
            $this->getEventRegistrationMock($this->getEventMock('Meeting de Brooklyn'), new \DateTimeImmutable('2017-08-06')),
        ], [
            $this->getEventMock('Réunion de réflexion parisienne', new \DateTime('2017-08-10')),
            $this->getEventMock('Nettoyage de la Kilchberg', new \DateTime('2017-07-17')),
            $this->getEventMock('Meeting de Brooklyn', new \DateTime('2017-08-01')),
        ]);
        $logsToArray = iterator_to_array($collection->getLogs());

        $this->assertCount(7, $logsToArray);
        $this->assertSame(3, $collection->getEventParticipationsCount());

        $this->assertSame('A participé à l\'événement "Réunion de réflexion parisienne" (11/08/2017)', $logsToArray[0]);
        $this->assertSame('A créé l\'événement "Réunion de réflexion parisienne" (10/08/2017)', $logsToArray[1]);
        $this->assertSame('A rejoint le mouvement En Marche (06/08/2017)', $logsToArray[2]);
        $this->assertSame('A participé à l\'événement "Meeting de Brooklyn" (06/08/2017)', $logsToArray[3]);
        $this->assertSame('A participé à l\'événement "Nettoyage de la Kilchberg" (02/08/2017)', $logsToArray[4]);
        $this->assertSame('A créé l\'événement "Meeting de Brooklyn" (01/08/2017)', $logsToArray[5]);
        $this->assertSame('A créé l\'événement "Nettoyage de la Kilchberg" (17/07/2017)', $logsToArray[6]);
    }

    private function getAdherentMock(\DateTime $registeredAt = null): Adherent
    {
        $adherent = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $adherent->expects(static::any())->method('getRegisteredAt')->willReturn($registeredAt);

        return $adherent;
    }

    private function getEventRegistrationMock(BaseEvent $event, \DateTimeImmutable $attendedAt): EventRegistration
    {
        $eventRegistration = $this->getMockBuilder(EventRegistration::class)->disableOriginalConstructor()->getMock();
        $eventRegistration->expects(static::any())->method('getEvent')->willReturn($event);
        $eventRegistration->expects(static::any())->method('getAttendedAt')->willReturn($attendedAt);

        return $eventRegistration;
    }

    private function getEventMock(string $name, \DateTime $createdAt = null): BaseEvent
    {
        $event = $this->getMockBuilder(BaseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects(static::any())->method('__toString')->willReturn($name);
        $event->expects(static::any())->method('getCreatedAt')->willReturn($createdAt);

        return $event;
    }
}
