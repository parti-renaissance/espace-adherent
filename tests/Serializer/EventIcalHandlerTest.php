<?php

namespace Tests\AppBundle\Serializer;

use AppBundle\Entity\Event;
use AppBundle\Entity\MunicipalEvent;
use AppBundle\Serializer\EventICalHandler;
use AppBundle\Serializer\IcalSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class EventIcalHandlerTest extends TestCase
{
    /**
     * @var EventICalHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->handler = new EventICalHandler();
    }

    public function testGetSubscribingMethods()
    {
        $result = EventICalHandler::getSubscribingMethods();

        $this->assertCount(2, $result);

        $this->assertEquals(Event::class, $result[0]['type']);
        $this->assertEquals('ical', $result[0]['format']);
        $this->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $result[0]['direction']);
        $this->assertEquals('serialize', $result[0]['method']);

        $this->assertEquals(MunicipalEvent::class, $result[1]['type']);
        $this->assertEquals('ical', $result[1]['format']);
        $this->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $result[1]['direction']);
        $this->assertEquals('serialize', $result[1]['method']);
    }

    /**
     * Since the model does not require an organizer, contact informations should not be available.
     */
    public function testSerializeNoOrganizer()
    {
        $visitor = $this->createMock(IcalSerializationVisitor::class);
        $committeeEvent = $this->createMock(Event::class);
        $uuid = $this->createMock(UuidInterface::class);
        $type = [];
        $serializationContext = $this->createMock(SerializationContext::class);
        $startDate = $this->createMock('\DateTime');
        $endDate = $this->createMock('\DateTime');

        $startDate->expects($this->once())
            ->method('format')
            ->with($this->equalTo('Ymd\THis'))
            ->will($this->returnValue('20170307T090000'))
        ;
        $endDate->expects($this->once())
            ->method('format')
            ->with($this->equalTo('Ymd\THis'))
            ->will($this->returnValue('20170307T100000'))
        ;

        $committeeEvent->expects($this->once())
                       ->method('getUuid')
                       ->will($this->returnValue($uuid))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getLocalBeginAt')
                       ->will($this->returnValue($startDate))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getFinishAt')
                       ->will($this->returnValue($endDate))
        ;
        $committeeEvent->expects($this->once())
                       ->method('getOrganizer')
                       ->will($this->returnValue(null))
        ;

        $visitor->expects($this->once())
                ->method('setRoot')
        ;

        $this->handler->serialize($visitor, $committeeEvent, $type, $serializationContext);
    }
}
