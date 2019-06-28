<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventPermissions;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use AppBundle\Security\Voter\AttendEventVoter;

class AttendEventVoterTest extends AbstractAdherentVoterTest
{
    /**
     * @var EventRegistrationRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registrationRepository;

    protected function setUp(): void
    {
        $this->registrationRepository = $this->createMock(EventRegistrationRepository::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->registrationRepository = null;

        parent::tearDown();
    }

    public function provideAnonymousCases(): iterable
    {
        yield [false, true, CitizenActionPermissions::UNREGISTER, $this->createMock(CitizenAction::class)];
        yield [false, true, EventPermissions::UNREGISTER, $this->createMock(Event::class)];
    }

    /**
     * @dataProvider provideRegisteredEventCases
     */
    public function testAdherentIsGrantedToUnregisterIfRegistered(bool $granted, string $attribute, $subject)
    {
        $adherent = $this->createAdherentMock();
        $adherent->expects($this->once())
            ->method('getEmailAddress')
        ;
        $this->registrationRepository->expects($this->once())
            ->method('findByRegisteredEmailAndEvent')
            ->with($adherent, $subject)
            ->willReturn($granted ? $this->createMock(EventRegistration::class) : null)
        ;

        $this->assertGrantedForAdherent($granted, true, $adherent, $attribute, $subject);
    }

    public function provideRegisteredEventCases(): iterable
    {
        $action = $this->createMock(CitizenAction::class);
        $event = $this->createMock(Event::class);

        yield [true, CitizenActionPermissions::UNREGISTER, $action];
        yield [false, CitizenActionPermissions::UNREGISTER, $action];
        yield [true, EventPermissions::UNREGISTER, $event];
        yield [false, EventPermissions::UNREGISTER, $event];
    }

    protected function getVoter(): AbstractAdherentVoter
    {
        return new AttendEventVoter($this->registrationRepository);
    }
}
