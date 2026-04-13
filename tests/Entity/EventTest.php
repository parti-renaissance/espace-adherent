<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Entity\NullablePostAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventTest extends TestCase
{
    #[DataProvider('provideNotFinishedEventDate')]
    public function testEventIsNotConsideredFinished(string $timeZone, string $date): void
    {
        $address = $this->createStub(NullablePostAddress::class);

        $event = new Event(Uuid::uuid4());
        $event->setAuthor($this->createStub(Adherent::class));
        $event->setCategory($this->createStub(EventCategory::class));
        $event->setCapacity(2);
        $event->setPostAddress($address);
        $event->setBeginAt(new \DateTime($date));
        $event->setFinishAt(new \DateTime('+1 hour'));
        $event->setTimeZone($timeZone);

        $this->assertFalse($event->isFinished());
    }

    public static function provideNotFinishedEventDate(): array
    {
        return [
            ['Europe/Paris', '+3 hours'],
            ['Europe/Paris', '+24 hours'],
            ['America/New_York', '-1 hours'],
            ['America/Indiana/Indianapolis', '-3 hours'],
            ['America/Los_Angeles', '-6 hours'],
        ];
    }

    #[DataProvider('provideFinishedEventDate')]
    public function testEventIsConsideredFinished(string $country, string $date): void
    {
        $address = $this->createStub(NullablePostAddress::class);
        $address->method('getCountry')->willReturn($country);

        $event = new Event(Uuid::uuid4());
        $event->setAuthor($this->createStub(Adherent::class));
        $event->setCategory($this->createStub(EventCategory::class));
        $event->setCapacity(2);
        $event->setPostAddress($address);
        $event->setFinishAt(new \DateTime($date));

        $this->assertTrue($event->isFinished());
    }

    public static function provideFinishedEventDate(): array
    {
        return [
            ['FR', '-2 hours'],
            ['FR', '-24 hours'],
            ['RU', '-5 hour'],
            ['AU', '-3 hours'],
            ['AU', '-11 hours'],
            ['SG', '-8 hours'],
            ['US', '-12 hours'],
            ['US', '-24 hours'],
        ];
    }

    public function testIsFull(): void
    {
        $event = new Event(Uuid::uuid4());
        $event->setAuthor($this->createStub(Adherent::class));
        $event->setCapacity(2);

        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertTrue($event->isFull());
    }

    public function testSetNationalOnNewEventAutomaticallyPinsIt(): void
    {
        $event = new Event(Uuid::uuid4());

        $this->assertFalse($event->pinned);

        $event->setNational(true);

        $this->assertTrue($event->isNational());
        $this->assertTrue($event->pinned);
    }

    public function testSetNationalOnPersistedEventDoesNotForcePin(): void
    {
        $event = new Event(Uuid::uuid4());

        $idProperty = new \ReflectionProperty($event, 'id');
        $idProperty->setValue($event, 42);

        $this->assertFalse($event->pinned);

        $event->setNational(true);

        $this->assertTrue($event->isNational());
        $this->assertFalse($event->pinned);
    }
}
