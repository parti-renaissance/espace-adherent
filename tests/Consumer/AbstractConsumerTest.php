<?php

namespace Tests\App\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @group membership
 */
class AbstractConsumerTest extends TestCase
{
    const CLASS_NAME = 'App\Consumer\AbstractConsumer';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ValidatorInterface
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private $entityManager;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testExecuteWithInvalidMessageBody()
    {
        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = 'toto';

        $this->setOutputCallback(function () {
        });
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('Message is not valid JSON', ['message' => $message->body]);
        $abstractConsumer->method('getLogger')->willReturn($logger);

        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testExecuteWithMessageViolation()
    {
        $violation = $this->getMockBuilder(ConstraintViolationInterface::class)->getMockForAbstractClass();
        $violation->expects($this->once())->method('getPropertyPath')->willReturn('name');
        $violation->expects($this->once())->method('getMessage')->willReturn('is missing');
        $collections = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['toto', ['tata']]);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('Message structure is not valid', [
            'message' => $message->body,
            'violations' => ['name' => ['is missing']],
        ]);
        $abstractConsumer->method('getLogger')->willReturn($logger);
        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testWriteln()
    {
        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass()
        ;

        $this->expectOutputString(sprintf('%s | %s', 'Mon message', 'mon output').\PHP_EOL);
        $abstractConsumer->writeln('Mon message', 'mon output');
    }
}
