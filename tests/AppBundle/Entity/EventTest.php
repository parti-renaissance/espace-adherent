<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\PostAddress;
use Ramsey\Uuid\UuidInterface;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testIsFinished()
    {
        $event = new Event(
            $this->getMockBuilder(UuidInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock(),
            null,
            '',
            '',
            '',
            $this->getMockBuilder(PostAddress::class)->disableOriginalConstructor()->getMock(),
            '',
            'tomorrow'
        );

        $this->assertFalse($event->isFinished());

        $event = new Event(
            $this->getMockBuilder(UuidInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock(),
            null,
            '',
            '',
            '',
            $this->getMockBuilder(PostAddress::class)->disableOriginalConstructor()->getMock(),
            '',
            'yesterday'
        );

        $this->assertTrue($event->isFinished());
    }

    public function testIsFull()
    {
        $event = new Event(
            $this->getMockBuilder(UuidInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock(),
            null,
            '',
            '',
            '',
            $this->getMockBuilder(PostAddress::class)->disableOriginalConstructor()->getMock(),
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
            $this->getMockBuilder(UuidInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock(),
            null,
            '',
            '',
            '',
            $this->getMockBuilder(PostAddress::class)->disableOriginalConstructor()->getMock(),
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
