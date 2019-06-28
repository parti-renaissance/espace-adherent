<?php

namespace Tests\AppBundle\Consumer;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Consumer\ProjectCitizenCreationNotificationConsumer;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Repository\CitizenProjectRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectCitizenCreationNotificationConsumerTest extends TestCase
{
    const CLASS_NAME = 'AppBundle\Consumer\ProjectCitizenCreationNotificationConsumer';

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

    public function testDoExecuteProjectCitizenNotFoundByUuidInMessage()
    {
        $uuid = 'aze-aze';

        $citizenProjectRepository = $this->createMock(CitizenProjectRepository::class);
        $citizenProjectRepository->expects($this->once())->method('findOneByUuid')->willReturn(null);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $projectCitizenCreationNotificationConsumer = new ProjectCitizenCreationNotificationConsumer($this->validator, $this->entityManager);

        $message = $this->createMock(AMQPMessage::class);
        $messageContent = ['uuid' => $uuid];
        $message->body = json_encode($messageContent);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('citizen project with '.$uuid.' not found', $messageContent);
        $projectCitizenCreationNotificationConsumer->setLogger($logger);
        $projectCitizenCreationNotificationConsumer->setCitizenProjectRepository($citizenProjectRepository);

        $this->expectOutputString('citizen project not found | citizen project with '.$uuid.' uuid not found'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $projectCitizenCreationNotificationConsumer->execute($message));
    }

    public function testDoExecuteWithNoMessageToSend()
    {
        $uuid = 'aze-aze';
        $messageContent = [
            'uuid' => $uuid,
            'offset' => 0,
        ];
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProjectRepository = $this->createMock(CitizenProjectRepository::class);
        $citizenProjectRepository->expects($this->once())->method('findOneByUuid')->willReturn($citizenProject);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())->method('count')->willReturn(0);

        $citizenProjectManager = $this->createMock(CitizenProjectManager::class);
        $citizenProjectManager->expects($this->once())->method('findAdherentNearCitizenProjectOrAcceptAllNotification')->with($citizenProject, $messageContent['offset'])->willReturn($paginator);
        $projectCitizenCreationNotificationConsumer = new ProjectCitizenCreationNotificationConsumer($this->validator, $this->entityManager);

        $message = $this->createMock(AMQPMessage::class);

        $message->body = json_encode($messageContent);

        $projectCitizenCreationNotificationConsumer->setCitizenProjectRepository($citizenProjectRepository);
        $projectCitizenCreationNotificationConsumer->setCitizeProjectManager($citizenProjectManager);

        $this->expectOutputString('info | No adherent to notify found for '.$uuid.' citizen project'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $projectCitizenCreationNotificationConsumer->execute($message));
    }

    public function testDoExecuteWithMessageToSendWithChunk()
    {
        $uuid = 'aze-aze';
        $messageContent = [
            'uuid' => $uuid,
            'offset' => 0,
        ];

        $uuid = $this->createMock(Uuid::class);
        $uuid->expects($this->once())->method('toString')->willReturn($uuid);
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->expects($this->once())->method('getUuid')->willReturn($uuid);

        $citizenProjectRepository = $this->createMock(CitizenProjectRepository::class);
        $citizenProjectRepository->expects($this->once())->method('findOneByUuid')->willReturn($citizenProject);

        $collections = $this->createMock(ConstraintViolationListInterface::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator->expects($this->once())->method('validate')->willReturn($collections);

        $paginator = $this->createMock(Paginator::class);
        $paginator->expects($this->once())->method('count')->willReturn(CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE + 1);

        $recipientsChunk1 = [];
        $recipientsChunk2 = [];
        for ($i = 0; $i < CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE; ++$i) {
            $recipientsChunk1[] = $this->createMock(Adherent::class);
        }
        $recipientsChunk2[] = $this->createMock(Adherent::class);

        $paginator->expects($this->at(1))->method('getIterator')->willReturn(new \ArrayIterator($recipientsChunk1));
        $paginator->expects($this->at(2))->method('getIterator')->willReturn(new \ArrayIterator($recipientsChunk2));

        $citizenProjectManager = $this->createMock(CitizenProjectManager::class);
        $citizenProjectManager->expects($this->exactly(3))->method('findAdherentNearCitizenProjectOrAcceptAllNotification')->willReturn($paginator);
        $citizenProjectManager->expects($this->once())->method('getCitizenProjectCreator')->with($citizenProject)->willReturn($this->createMock(Adherent::class));
        $citizenProjectMessageNotifier = $this->createMock(CitizenProjectMessageNotifier::class);
        $citizenProjectMessageNotifier->expects($this->exactly(CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE + 1))->method('sendAdherentNotificationCreation');

        $projectCitizenCreationNotificationConsumer = new ProjectCitizenCreationNotificationConsumer($this->validator, $this->entityManager);

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode($messageContent);

        $projectCitizenCreationNotificationConsumer->setCitizenProjectRepository($citizenProjectRepository);
        $projectCitizenCreationNotificationConsumer->setCitizeProjectManager($citizenProjectManager);
        $projectCitizenCreationNotificationConsumer->setCitizenProjectMessageNotifier($citizenProjectMessageNotifier);
        $this->expectOutputString('info | Start sending. offset : 0 | totalAdherent : '.(CitizenProjectMessageNotifier::NOTIFICATION_PER_PAGE + 1).' | citizenProjectUuid '.$uuid.\PHP_EOL.'success | Message correctly send from offset '.$messageContent['offset'].' to the end'.\PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $projectCitizenCreationNotificationConsumer->execute($message));
    }
}
