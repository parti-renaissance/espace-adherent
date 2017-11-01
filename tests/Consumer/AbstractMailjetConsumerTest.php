<?php

namespace Tests\AppBundle\Consumer;

use AppBundle\Entity\MailjetEmail;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Mailjet\ClientInterface;
use AppBundle\Mailjet\Exception\MailjetException;
use AppBundle\Repository\MailjetEmailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ConnectException;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractMailjetConsumerTest extends TestCase
{
    const CLASS_NAME = 'AppBundle\Consumer\AbstractMailjetConsumer';

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

    public function testDoExecuteWithoutUuidInMessage()
    {
        $uuid = 'aze-aze';

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository->expects($this->once())->method('findOneByUuid')->willReturn(null);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getMailjetRepository'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())->method('error')->with('MailjetEmail not found', ['uuid' => $uuid]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->once())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);

        $this->expectOutputString($uuid.' | MailjetEmail not found, rejecting'.PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteWithInvalidUuidFormatInMessage()
    {
        $uuid = 'aze-aze';
        $invalidUuidException = $this
            ->getMockBuilder(InvalidUuidException::class)
            ->setConstructorArgs([
                'message' => sprintf('Uuid "%s" is not valid.', $uuid),
            ])
            ->getMock();

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willThrowException($invalidUuidException);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getMailjetRepository'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger
            ->expects($this->once())
            ->method('error')
            ->with('UUID is invalid format', ['exception' => $invalidUuidException]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->once())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);

        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteNotDelivery()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePaylod = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePaylod);

        $mailjetEmail = $this->getMockBuilder(MailjetEmail::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson'])
            ->getMock();

        $mailjetEmail
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %s recipients', $messageClass, $sender, count($recipients)));

        $mailjetEmail
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded);

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($mailjetEmail);

        $mailjetClient = $this->createMock(ClientInterface::class);
        $mailjetClient
            ->expects($this->once())
            ->method('sendEmail')
            ->willReturn(false)
            ->with($messagePayloadEncoded);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getMailjetRepository', 'getMailjetClient'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);

        $abstractConsumer->expects($this->once())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);
        $abstractConsumer->expects($this->once())->method('getMailjetClient')->willReturn($mailjetClient);
        $this->expectOutputString($uuid.' | Delivering '.sprintf('%s from %s to %s recipients', $messageClass, $sender, count($recipients)).PHP_EOL.$uuid.' | An issue occured, requeuing'.PHP_EOL);

        $this->assertSame(ConsumerInterface::MSG_REJECT_REQUEUE, $abstractConsumer->execute($message));
    }

    public function testDoExecuteSuccessDelivery()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePaylod = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePaylod);

        $mailjetEmail = $this->getMockBuilder(MailjetEmail::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock();

        $mailjetEmail
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, count($recipients)));

        $mailjetEmail
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded);

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($mailjetEmail);

        $mailjetEmailRepository
            ->expects($this->once())
            ->method('setDelivered')
            ->with($mailjetEmail, true);

        $mailjetClient = $this->createMock(ClientInterface::class);
        $mailjetClient
            ->expects($this->once())
            ->method('sendEmail')
            ->willReturn(true)
            ->with($messagePayloadEncoded);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getMailjetRepository', 'getMailjetClient'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);

        $abstractConsumer->expects($this->any())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);
        $abstractConsumer->expects($this->once())->method('getMailjetClient')->willReturn($mailjetClient);
        $this->expectOutputString($uuid.' | Delivering '.sprintf('%s from %s to %i recipients', $messageClass, $sender, count($recipients)).PHP_EOL);

        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }

    public function testDoExecuteMailJetException()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePaylod = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePaylod);

        $mailjetEmail = $this->getMockBuilder(MailjetEmail::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock();

        $mailjetEmail
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, count($recipients)));

        $mailjetEmail
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded);

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($mailjetEmail);

        $mailjetException = $this
            ->getMockBuilder(MailjetException::class)
            ->setConstructorArgs([
                'message' => 'Unable to send email to recipients.',
            ])
            ->getMock();

        $mailjetClient = $this->createMock(ClientInterface::class);
        $mailjetClient
            ->expects($this->once())
            ->method('sendEmail')
            ->with($messagePayloadEncoded)
            ->willThrowException($mailjetException);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getMailjetRepository', 'getMailjetClient'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger->expects($this->once())->method('error')->with('Unable to send email to recipients.', ['exception' => $mailjetException]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->any())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);
        $abstractConsumer->expects($this->once())->method('getMailjetClient')->willReturn($mailjetClient);
        $this->assertSame(ConsumerInterface::MSG_REJECT_REQUEUE, $abstractConsumer->execute($message));
    }

    public function testDoExecuteGuzzleException()
    {
        $uuid = '0c78bf78-952e-49dc-8f85-611b7f6d4050';
        $messageClass = 'Hello World';
        $sender = 's.jobs@pomme.com';
        $recipients = ['b.gates@petit-logiciel.com'];
        $messagePaylod = 'you are fired';
        $messagePayloadEncoded = base64_encode($messagePaylod);

        $mailjetEmail = $this->getMockBuilder(MailjetEmail::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEnglishLog', 'getRequestPayloadJson', 'delivered'])
            ->getMock();

        $mailjetEmail
            ->expects($this->once())
            ->method('getEnglishLog')
            ->willReturn(sprintf('%s from %s to %i recipients', $messageClass, $sender, count($recipients)));

        $mailjetEmail
            ->expects($this->once())
            ->method('getRequestPayloadJson')
            ->willReturn($messagePayloadEncoded);

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($mailjetEmail);

        $connectException = $this->createMock(ConnectException::class);

        $mailjetClient = $this->createMock(ClientInterface::class);
        $mailjetClient
            ->expects($this->once())
            ->method('sendEmail')
            ->with($messagePayloadEncoded)
            ->willThrowException($connectException);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getLogger', 'getMailjetRepository', 'getMailjetClient'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $this->setOutputCallback(function () {
        });

        $logger->expects($this->once())->method('error')->with('RabbitMQ connection timeout while sending a mail with UUID '.$uuid, ['exception' => $connectException]);
        $abstractConsumer->expects($this->once())->method('getLogger')->willReturn($logger);
        $abstractConsumer->expects($this->any())->method('getMailjetRepository')->willReturn($mailjetEmailRepository);
        $abstractConsumer->expects($this->once())->method('getMailjetClient')->willReturn($mailjetClient);
        $this->assertSame(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }
}
