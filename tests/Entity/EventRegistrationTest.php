<?php

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventRegistrationTest extends TestCase
{
    public const REGISTRATION_UUID = '75aac96a-9cba-4bd8-91f4-414d269ca0b0';

    public const EVENT_1_UUID = 'a103e823-1bd1-406d-81ec-d1f764437d1b';
    public const EVENT_2_UUID = 'd431b756-2428-423c-ab43-1ee25871b77f';

    public const ADHERENT_1_UUID = '0936205b-35fb-4250-a97e-bfc3a2bcba12';
    public const ADHERENT_2_UUID = '59e4203a-cf4a-4a39-a5f1-768d46c3575e';

    public function testCreateEventRegistrationForGuest()
    {
        $registration = new EventRegistration(
            Uuid::fromString(self::REGISTRATION_UUID),
            $event = $this->createEventMock(self::EVENT_2_UUID),
            'Rose',
            'Leroy',
            'rose-lr@domain.tld',
            null,
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

        $event = $this->createPartialMock(Event::class, ['getUuid']);

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
