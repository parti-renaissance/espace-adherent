<?php

namespace Tests\AppBundle\Consumer;

use AppBundle\Consumer\ReferentMessageDispatcherConsumer;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentManagedUsersMessage;
use AppBundle\Mailer\MailerService;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Repository\ReferentManagedUsersMessageRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReferentMessageDispatcherConsumerTest extends TestCase
{
    const CLASS_NAME = 'AppBundle\Consumer\ReferentMessageDispatcherConsumer';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ValidatorInterface
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface
     */
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;

        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager = null;
        $this->validator = null;
    }

    public function testDoExecuteReferentNotFoundByUuidInMessage()
    {
        $uuid = 'aze-aze';

        $referentMessageRepository = $this->createMock(ReferentManagedUsersMessageRepository::class);
        $referentMessageRepository->expects($this->once())->method('findOneByUuid')->willReturn(null);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $referentMessageDispatcherConsumer = new ReferentMessageDispatcherConsumer($this->validator, $this->entityManager);

        $message = $this->createMock(AMQPMessage::class);
        $messageContent = ['uuid' => $uuid];
        $message->body = json_encode($messageContent);
        $logger = $this->createMock(LoggerInterface::class);

        $referentMessageDispatcherConsumer->setLogger($logger);
        $referentMessageDispatcherConsumer->setReferentMessageRepository($referentMessageRepository);

        $this->expectOutputString($uuid.' | Referent message not found, rejecting'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    public function testDoExecuteWithNoMessageToSend()
    {
        $uuid = 'aze-aze';
        $messageContent = ['uuid' => $uuid];

        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('hello@world.com');

        $referentMessage = $this->createMock(ReferentManagedUsersMessage::class);
        $referentMessage->expects($this->any())->method('getFrom')->willReturn($adherent);

        $referentMessageRepository = $this->createMock(ReferentManagedUsersMessageRepository::class);
        $referentMessageRepository->expects($this->once())->method('findOneByUuid')->willReturn($referentMessage);

        $iterableResult = $this->createMock(IterableResult::class);

        $referentManagedUserRepository = $this->createMock(ReferentManagedUserRepository::class);
        $referentManagedUserRepository->expects($this->once())->method('createDispatcherIterator')->willReturn($iterableResult);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $referentMessageDispatcherConsumer = $this
            ->getMockBuilder(ReferentMessageDispatcherConsumer::class)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['getReferentManagedUserRepository', 'getReferentMessageRepository'])
            ->getMock()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $logger = $this->createMock(LoggerInterface::class);

        $referentMessageDispatcherConsumer->setLogger($logger);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getReferentManagedUserRepository')->willReturn($referentManagedUserRepository);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getReferentMessageRepository')->willReturn($referentMessageRepository);
        $this->expectOutputString($uuid.' | Dispatching message from hello@world.com'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    public function testDoExecuteWithMessageToSendWithChunk()
    {
        $uuid = 'aze-aze';
        $messageContent = ['uuid' => $uuid];

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->any())->method('sendMessage')->willReturn(true);

        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->any())->method('getEmailAddress')->willReturn('hello@world.com');

        $uuidInterface = $this->createMock(UuidInterface::class);
        $uuidInterface->expects($this->any())->method('toString')->willReturn($uuid);

        $referentMessage = $this->createMock(ReferentManagedUsersMessage::class);
        $referentMessage->expects($this->any())->method('getFrom')->willReturn($adherent);
        $referentMessage->expects($this->any())->method('getUuid')->willReturn($uuidInterface);

        $referentMessageRepository = $this->createMock(ReferentManagedUsersMessageRepository::class);
        $referentMessageRepository->expects($this->once())->method('findOneByUuid')->willReturn($referentMessage);

        $recipients = [];
        for ($i = 0; $i < (MailerService::PAYLOAD_MAXSIZE + 10); ++$i) {
            $recipient = $this->createMock(ReferentManagedUser::class);
            $recipient->expects($this->any())->method('getEmail')->willReturn(uniqid().'@tld.com');

            $recipients[$i][] = $recipient;
        }
        $iterableResult = $this->createIterator($recipients);

        $referentManagedUserRepository = $this->createMock(ReferentManagedUserRepository::class);
        $referentManagedUserRepository->expects($this->once())->method('createDispatcherIterator')->willReturn($iterableResult);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $referentMessageDispatcherConsumer = $this
            ->getMockBuilder(ReferentMessageDispatcherConsumer::class)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods([
                'getReferentMessageRepository',
                'getReferentManagedUserRepository',
                'getManager',
                'getMailer',
            ])
            ->getMock()
        ;

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $logger = $this->createMock(LoggerInterface::class);

        $referentMessageDispatcherConsumer->setLogger($logger);
        $referentMessageDispatcherConsumer->expects($this->any())->method('getMailer')->willReturn($mailer);
        $referentMessageDispatcherConsumer->expects($this->any())->method('getReferentManagedUserRepository')->willReturn($referentManagedUserRepository);
        $referentMessageDispatcherConsumer->expects($this->any())->method('getReferentMessageRepository')->willReturn($referentMessageRepository);
        $referentMessageDispatcherConsumer->expects($this->any())->method('getManager')->willReturn($this->entityManager);

        $this->expectOutputString(
            $uuid.' | Dispatching message from hello@world.com'.\PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (50)'.\PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (60)'.\PHP_EOL
        );
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    private function createIterator(array $data): \PHPUnit_Framework_MockObject_MockObject
    {
        $mock = $this
            ->getMockBuilder(IterableResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['rewind', 'current', 'key', 'next', 'valid', 'count'])
            ->getMock()
        ;
        $iteratorData = new \stdClass();
        $iteratorData->array = $data;
        $iteratorData->position = 0;

        $mock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        ++$iteratorData->position;
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            )
        ;

        $mock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return \count($iteratorData->array);
                    }
                )
            )
        ;

        return $mock;
    }
}
