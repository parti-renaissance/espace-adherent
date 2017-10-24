<?php
namespace Tests\AppBundle\Consumer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractConsumerTest extends TestCase
{
    const CLASS_NAME = 'AppBundle\Consumer\AbstractConsumer';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->method('getManager')->willReturn($this->objectManager);
    }

    public function testExecuteWithInvalidMessageBody()
    {
        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->registry])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = 'toto';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('Message is not valid JSON', ['message' => $message->body]);
        $abstractConsumer->method('getLogger')->willReturn($logger);

        $this->assertEquals(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testExecuteWithMessageViolation()
    {
        $violation = $this->getMockBuilder(ConstraintViolationInterface::class)->getMockForAbstractClass();
        $violation->expects($this->once())->method('getPropertyPath')->willReturn('name');
        $violation->expects($this->once())->method('getMessage')->willReturn('is missing');
        $collections = new ArrayCollection([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->registry])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['toto', ['tata']]);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('Message structure is not valid', [
            'message' => $message->body,
            'violations' => ['name' => ['is missing']]
        ]);
        $abstractConsumer->method('getLogger')->willReturn($logger);
        $this->assertEquals(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testWriteln()
    {
        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->registry])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass();

        $this->expectOutputString(sprintf('%s | %s', 'Mon message', 'mon output').PHP_EOL);
        $abstractConsumer->writeln('Mon message', 'mon output');
    }

}
