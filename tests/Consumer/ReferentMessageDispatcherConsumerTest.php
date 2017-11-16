<?php

namespace Tests\AppBundle\Consumer;

use AppBundle\Consumer\ReferentMessageDispatcherConsumer;
use AppBundle\Entity\Adherent;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\ReferentMessage as Message;
use AppBundle\Referent\ReferentMessage;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
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

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testDoExecuteReferentNotFoundByUuidInMessage()
    {
        $uuid = 'aze-aze';

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects($this->once())->method('findByUuid')->willReturn(null);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $referentMessageDispatcherConsumer = new ReferentMessageDispatcherConsumer($this->validator, $this->entityManager);

        $message = $this->createMock(AMQPMessage::class);
        $messageContent = ['referent_uuid' => $uuid];
        $message->body = json_encode($messageContent);
        $logger = $this->createMock(LoggerInterface::class);

        $referentMessageDispatcherConsumer->setLogger($logger);
        $referentMessageDispatcherConsumer->setAdherentRepository($adherentRepository);

        $this->expectOutputString($uuid.' | Referent not found, rejecting'.PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    public function testDoExecuteWithNoMessageToSend()
    {
        $uuid = 'aze-aze';
        $messageContent = ['referent_uuid' => $uuid];

        $referentMessage = $this->createMock(ReferentMessage::class);

        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('hello@world.com');

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects($this->once())->method('findByUuid')->willReturn($adherent);

        $iterableResult = $this->createMock(IterableResult::class);

        $referentManagedUserRepository = $this->createMock(ReferentManagedUserRepository::class);
        $referentManagedUserRepository->expects($this->once())->method('createDispatcherIterator')->willReturn($iterableResult);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $referentMessageDispatcherConsumer = $this
            ->getMockBuilder(ReferentMessageDispatcherConsumer::class)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods(['createReferentMessage', 'getAdherentRepository', 'getReferentManagedUserRepository'])
            ->getMock();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $referentMessageDispatcherConsumer->expects($this->once())->method('getAdherentRepository')->willReturn($adherentRepository);
        $referentMessageDispatcherConsumer->expects($this->any())->method('createReferentMessage')->willReturn($referentMessage);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getReferentManagedUserRepository')->willReturn($referentManagedUserRepository);
        $this->expectOutputString($uuid.' | Dispatching message from hello@world.com'.PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    public function testDoExecuteWithMessageToSendWithChunk()
    {
        $uuid = 'aze-aze';
        $messageContent = ['referent_uuid' => $uuid];

        $referentMessage = $this->getMockBuilder(ReferentMessage::class)->disableOriginalConstructor()->getMock();

        $mailer = $this->getMockBuilder(MailerService::class)->disableOriginalConstructor()->setMethods(['sendMessage'])->getMock();

        $uuidInterface = $this->createMock(UuidInterface::class);
        $uuidInterface->expects($this->any())->method('toString')->willReturn($uuid);

        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->any())->method('getEmailAddress')->willReturn('hello@world.com');
        $adherent->expects($this->any())->method('getUuid')->willReturn($uuidInterface);
        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects($this->once())->method('findByUuid')->willReturn($adherent);

        $recipients = [];
        for ($i = 0; $i < (MailerService::PAYLOAD_MAXSIZE + 10); ++$i) {
            $recipients[$i][] = uniqid().'@tld.com';
        }
        $iterableResult = $this->createIterator($recipients);

        $referentManagedUserRepository = $this->createMock(ReferentManagedUserRepository::class);
        $referentManagedUserRepository->expects($this->once())->method('createDispatcherIterator')->willReturn($iterableResult);

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $miljetReferentMessage = new Message($uuidInterface, 'a', 'b', 'c', 'd');
        $referentMessageDispatcherConsumer = $this
            ->getMockBuilder(ReferentMessageDispatcherConsumer::class)
            ->setConstructorArgs([$this->validator, $this->entityManager])
            ->setMethods([
                'createReferentMessage',
                'getAdherentRepository',
                'getReferentManagedUserRepository',
                'createMailerReferentMessage',
                'getManager',
                'getMailer',
            ])
            ->getMock();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $referentMessageDispatcherConsumer->expects($this->any())->method('getMailer')->willReturn($mailer);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getAdherentRepository')->willReturn($adherentRepository);
        $referentMessageDispatcherConsumer->expects($this->any())->method('createReferentMessage')->willReturn($referentMessage);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getReferentManagedUserRepository')->willReturn($referentManagedUserRepository);
        $referentMessageDispatcherConsumer->expects($this->any())->method('createMailerReferentMessage')->willReturn($miljetReferentMessage);
        $referentMessageDispatcherConsumer->expects($this->once())->method('getManager')->willReturn($this->entityManager);
        $this->entityManager->expects($this->once())->method('clear');

        $this->expectOutputString(
            $uuid.' | Dispatching message from hello@world.com'.PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (50)'.PHP_EOL
            .$uuid.' | Message from hello@world.com dispatched (60)'.PHP_EOL
        );
        $this->assertSame(ConsumerInterface::MSG_ACK, $referentMessageDispatcherConsumer->execute($message));
    }

    private function createIterator(array $data): \PHPUnit_Framework_MockObject_MockObject
    {
        $mock = $this
            ->getMockBuilder(IterableResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['rewind', 'current', 'key', 'next', 'valid', 'count'])
            ->getMock();
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
            );

        $mock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $mock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $mock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        ++$iteratorData->position;
                    }
                )
            );

        $mock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $mock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return count($iteratorData->array);
                    }
                )
            );

        return $mock;
    }
}
