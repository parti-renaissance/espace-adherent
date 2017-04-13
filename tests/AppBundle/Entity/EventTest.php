<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\PostAddress;
use Ramsey\Uuid\UuidInterface;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideNotFinishedEventDate
     */
    public function testEventIsNotConsideredFinished(string $country, string $date)
    {
        $address = $this->createMock(PostAddress::class);
        $address->expects($this->any())->method('getCountry')->willReturn($country);

        $event = new Event(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            '',
            '',
            $address,
            '',
            $date
        );

        $this->assertFalse($event->isFinished());
    }

    public static function provideNotFinishedEventDate()
    {
        return [
            ['FR', '+3 hours'],
            ['FR', '+24 hours'],
            ['US', '-1 hours'],
            ['US', '-5 hours'],
            ['US', '-8 hours'],
        ];
    }

    /**
     * @dataProvider provideFinishedEventDate
     */
    public function testEventIsConsideredFinished(string $country, string $date)
    {
        $address = $this->createMock(PostAddress::class);
        $address->expects($this->any())->method('getCountry')->willReturn($country);

        $event = new Event(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            '',
            '',
            $address,
            '',
            $date
        );

        $this->assertTrue($event->isFinished());
    }

    public static function provideFinishedEventDate()
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
        $event = new Event(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            '',
            '',
            $this->createMock(PostAddress::class),
            '',
            '',
            2,
            null,
            null,
            0
        );
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertFalse($event->isFull());

        $event->incrementParticipantsCount();
        $this->assertTrue($event->isFull());

        $event = new Event(
            $this->createMock(UuidInterface::class),
            $this->createMock(Adherent::class),
            null,
            '',
            '',
            '',
            $this->createMock(PostAddress::class),
            '',
            '',
            null,
            null,
            null,
            10000000
        );
        $this->assertFalse($event->isFull());
    }
}
