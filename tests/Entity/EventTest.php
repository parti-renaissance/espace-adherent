<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Entity\NullablePostAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class EventTest extends TestCase
{
    #[DataProvider('provideNotFinishedEventDate')]
    public function testEventIsNotConsideredFinished(string $timeZone, string $date)
    {
        $address = $this->createMock(NullablePostAddress::class);

        $event = new Event($this->createMock(UuidInterface::class));
        $event->setAuthor($this->createMock(Adherent::class));
        $event->setCategory($this->createMock(EventCategory::class));
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
    public function testEventIsConsideredFinished(string $country, string $date)
    {
        $address = $this->createMock(NullablePostAddress::class);
        $address->expects($this->any())->method('getCountry')->willReturn($country);

        $event = new Event($this->createMock(UuidInterface::class));
        $event->setAuthor($this->createMock(Adherent::class));
        $event->setCategory($this->createMock(EventCategory::class));
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

    public function testIsFull()
    {
        $event = new Event($this->createMock(UuidInterface::class));
        $event->setAuthor($this->createMock(Adherent::class));
        $event->setCapacity(2);

        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertTrue($event->isFull());
    }
}
