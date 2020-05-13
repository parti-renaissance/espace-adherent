<?php

namespace Tests\App\Security\Voter;

use App\CitizenAction\CitizenActionPermissions;
use App\Entity\CitizenAction;
use App\Entity\Event;
use App\Event\EventPermissions;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\AttendEventVoter;

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

    protected function getVoter(): AbstractAdherentVoter
    {
        return new AttendEventVoter($this->registrationRepository);
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
        $email = $adherent->getEmailAddress();

        $adherent
            ->expects($this->once())
            ->method('getEmailAddress')
        ;

        $this->registrationRepository->expects($this->once())
            ->method('isAlreadyRegistered')
            ->with($email, $subject)
            ->willReturn($granted ? true : false)
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
}
