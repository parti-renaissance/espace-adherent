<?php

namespace Tests\AppBundle\Committee\Serializer;

use AppBundle\Committee\Serializer\CommitteeEventICalHandler;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Serializer\Visitor\IcalSerializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\SerializationContext;
use Ramsey\Uuid\UuidInterface;

class CommitteeEventICalHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommitteeEventICalHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->handler = new CommitteeEventICalHandler();
    }

    public function testGetSubscribingMethods()
    {
        $result = CommitteeEventICalHandler::getSubscribingMethods();

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $this->assertEquals(CommitteeEvent::class, $result[0]['type']);
        $this->assertEquals('ical', $result[0]['format']);
        $this->assertEquals(GraphNavigator::DIRECTION_SERIALIZATION, $result[0]['direction']);
        $this->assertEquals('serialize', $result[0]['method']);
    }

    /**
     * Since the model does not require an organizer, contact informations should not be available.
     */
    public function testSerializeNoOrganizer()
    {
        $visitor = $this->createMock(IcalSerializationVisitor::class);
        $committeeEvent = $this->createMock(CommitteeEvent::class);
        $uuid = $this->createMock(UuidInterface::class);
        $type = [];
        $serializationContext = $this->createMock(SerializationContext::class);
        $startDate = $this->createMock('\DateTime');
        $endDate = $this->createMock('\DateTime');

        $committeeEvent->expects($this->once())
            ->method('getUuid')
            ->will($this->returnValue($uuid));
        $committeeEvent->expects($this->once())
            ->method('getBeginAt')
            ->will($this->returnValue($startDate));
        $committeeEvent->expects($this->once())
            ->method('getFinishAt')
            ->will($this->returnValue($endDate));
        $committeeEvent->expects($this->once())
            ->method('getOrganizer')
            ->will($this->returnValue(null));

        $visitor->expects($this->once())
            ->method('setRoot');

        $this->handler->serialize($visitor, $committeeEvent, $type, $serializationContext);
    }
}
