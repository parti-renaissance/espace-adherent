<?php

namespace Tests\App\Security\Voter;

use App\Entity\Event\CommitteeEvent;
use App\Event\EventPermissions;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\AbstractAdherentVoter;
use App\Security\Voter\AttendEventVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

class AttendEventVoterTest extends AbstractAdherentVoterTestCase
{
    /**
     * @var EventRegistrationRepository|MockObject
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

    public static function provideAnonymousCases(): iterable
    {
        yield [false, true, EventPermissions::UNREGISTER, fn ($_this) => $_this->createMock(CommitteeEvent::class)];
    }

    #[DataProvider('provideRegisteredEventCases')]
    public function testAdherentIsGrantedToUnregisterIfRegistered(bool $granted, string $attribute)
    {
        $subject = $this->createMock(CommitteeEvent::class);
        $adherent = $this->createAdherentMock();
        $email = $adherent->getEmailAddress();

        $adherent
            ->expects($this->once())
            ->method('getEmailAddress')
        ;

        $this->registrationRepository->expects($this->once())
            ->method('isAlreadyRegistered')
            ->with($email, $subject)
            ->willReturn($granted)
        ;

        $this->assertGrantedForAdherent($granted, true, $adherent, $attribute, $subject);
    }

    public static function provideRegisteredEventCases(): iterable
    {
        yield [true, EventPermissions::UNREGISTER];
        yield [false, EventPermissions::UNREGISTER];
    }
}
