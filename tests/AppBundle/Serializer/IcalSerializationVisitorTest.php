<?php

namespace Tests\AppBundle\Serializer;

use AppBundle\Serializer\IcalSerializationVisitor;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

class IcalSerializationVisitorTest extends \PHPUnit_Framework_TestCase
{
    protected $propertyNamingStrategy;
    /**
     * @var IcalSerializationVisitor
     */
    protected $visitor;

    protected function setUp()
    {
        $this->propertyNamingStrategy = $this->createMock(PropertyNamingStrategyInterface::class);

        $this->visitor = new IcalSerializationVisitor($this->propertyNamingStrategy);
    }

    /**
     * Ensure a NULL value is returned when no data has been provided.
     */
    public function testGetResultEmptyData()
    {
        $this->assertNull($this->visitor->getResult());
    }

    /**
     * Ensure the output is a string.
     *
     * TODO Check generated content
     */
    public function testGetResult()
    {
        $eventData = ['VEVENT' => []];
        $this->visitor->setRoot($eventData);

        $this->assertInternalType('string', $this->visitor->getResult());
    }
}
