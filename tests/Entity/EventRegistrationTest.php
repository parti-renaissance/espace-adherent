<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Event;
use App\Entity\EventRegistration;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventRegistrationTest extends TestCase
{
    const REGISTRATION_UUID = '75aac96a-9cba-4bd8-91f4-414d269ca0b0';

    const EVENT_1_UUID = 'a103e823-1bd1-406d-81ec-d1f764437d1b';
    const EVENT_2_UUID = 'd431b756-2428-423c-ab43-1ee25871b77f';

    const ADHERENT_1_UUID = '0936205b-35fb-4250-a97e-bfc3a2bcba12';
    const ADHERENT_2_UUID = '59e4203a-cf4a-4a39-a5f1-768d46c3575e';

    public function testCreateEventRegistrationForRegisteredAdherent()
    {
        $registration = new EventRegistration(
            Uuid::fromString(self::REGISTRATION_UUID),
            $event = $this->createEventMock(self::EVENT_1_UUID),
            'Joseph',
            'Seguin',
            'joseph.seguin@domain.tld',
            false,
            Uuid::fromString(self::ADHERENT_1_UUID)
        );

        $this->assertEquals(Uuid::fromString(self::REGISTRATION_UUID), $registration->getUuid());
        $this->assertSame($event, $registration->getEvent());
        $this->assertSame('Joseph', $registration->getFirstName());
        $this->assertSame('Seguin', $registration->getLastName());
        $this->assertSame('joseph.seguin@domain.tld', $registration->getEmailAddress());
        $this->assertFalse($registration->isNewsletterSubscriber());

        $this->assertTrue($registration->matches($event, $this->createAdherentMock(self::ADHERENT_1_UUID)));
        $this->assertFalse($registration->matches($event, $this->createAdherentMock(self::ADHERENT_2_UUID)));
        $this->assertFalse($registration->matches($this->createEventMock(self::EVENT_2_UUID), $this->createAdherentMock(self::ADHERENT_1_UUID)));
        $this->assertFalse($registration->matches($this->createEventMock(self::EVENT_2_UUID), $this->createAdherentMock(self::ADHERENT_2_UUID)));
        $this->assertFalse($registration->matches($this->createEventMock(self::EVENT_2_UUID)));
    }

    public function testCreateEventRegistrationForGuest()
    {
        $registration = new EventRegistration(
            Uuid::fromString(self::REGISTRATION_UUID),
            $event = $this->createEventMock(self::EVENT_2_UUID),
            'Rose',
            'Leroy',
            'rose-lr@domain.tld',
            true
        );

        $this->assertEquals(Uuid::fromString(self::REGISTRATION_UUID), $registration->getUuid());
        $this->assertSame($event, $registration->getEvent());
        $this->assertSame('Rose', $registration->getFirstName());
        $this->assertSame('Leroy', $registration->getLastName());
        $this->assertSame('rose-lr@domain.tld', $registration->getEmailAddress());
        $this->assertTrue($registration->isNewsletterSubscriber());

        $this->assertTrue($registration->matches($event));
        $this->assertFalse($registration->matches($this->createEventMock(self::EVENT_1_UUID)));
        $this->assertFalse($registration->matches($event, $this->createAdherentMock(self::ADHERENT_1_UUID)));
        $this->assertFalse($registration->matches($event, $this->createAdherentMock(self::ADHERENT_2_UUID)));
    }

    private function createAdherentMock(string $uuid)
    {
        $adherent = $this
            ->getMockBuilder(Adherent::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $adherent->expects($this->any())->method('getUuid')->willReturn(Uuid::fromString($uuid));

        return $adherent;
    }

    private function createEventMock(string $uuid)
    {
        $uuid = Uuid::fromString($uuid);

        $event = $this
            ->getMockBuilder(Event::class)
            ->setMethods(['getUuid'])
            ->setMethodsExcept(['equals'])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        // Hack to ensure the $uuid protected property contains
        // a valid UuidInterface instance.
        $rp = new \ReflectionProperty($event, 'uuid');
        $rp->setAccessible(true);
        $rp->setValue($event, $uuid);
        $rp->setAccessible(false);

        $event->expects($this->any())->method('getUuid')->willReturn($uuid);

        return $event;
    }
}
