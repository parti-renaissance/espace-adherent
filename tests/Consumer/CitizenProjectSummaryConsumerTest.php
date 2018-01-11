<?php

namespace Tests\AppBundle\Consumer;

use AppBundle\CitizenProject\CitizenProjectBroadcaster;
use AppBundle\Consumer\CitizenProjectSummaryConsumer;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CitizenProjectSummaryConsumerTest extends TestCase
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var EntityManagerInterface */
    private $entityManager;

    private $broadcaster;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->broadcaster = $this->createMock(CitizenProjectBroadcaster::class);
    }

    public function tearDown()
    {
        $this->entityManager = null;
        $this->validator = null;
        $this->broadcaster = null;
    }

    public function testDoExecuteAdherentNotFoundByUuidInMessage()
    {
        $uuid = 'foo-bar-uuid';
        $approvedSince = '7 days';

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn(null)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Adherent::class)
            ->willReturn($adherentRepository)
        ;

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $citizenProjectSummaryConsumer = new CitizenProjectSummaryConsumer(
            $this->validator, $this->entityManager, $this->broadcaster
        );

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['adherent_uuid' => $uuid, 'approved_since' => $approvedSince]);

        $logger = $this->createMock(LoggerInterface::class);
        $citizenProjectSummaryConsumer->setLogger($logger);

        $this->expectOutputString('Adherent not found | Adherent with '.$uuid.' uuid not found'.PHP_EOL);
        $this->assertSame(ConsumerInterface::MSG_ACK, $citizenProjectSummaryConsumer->execute($message));
    }

    public function testDoExecuteWithBroadcast()
    {
        $email = 'jeanbernard@bonsoir.com';
        $uuid = Adherent::createUuid($email);
        $approvedSince = '7 days';

        $adherent = new Adherent(
            $uuid,
            $email,
            'password',
            'male',
            'Jean-Bernard',
            'Brioul',
            new \DateTime('1983-11-29'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($adherent)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Adherent::class)
            ->willReturn($adherentRepository)
        ;

        $collections = $this->createMock(ArrayCollection::class);
        $collections->expects($this->once())->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections)
        ;

        $this->broadcaster
            ->expects($this->once())
            ->method('broadcast')
            ->with($adherent, $approvedSince)
        ;

        $citizenProjectSummaryConsumer = new CitizenProjectSummaryConsumer(
            $this->validator, $this->entityManager, $this->broadcaster
        );

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['adherent_uuid' => $uuid, 'approved_since' => $approvedSince]);

        $logger = $this->createMock(LoggerInterface::class);
        $citizenProjectSummaryConsumer->setLogger($logger);
        $this->assertSame(ConsumerInterface::MSG_ACK, $citizenProjectSummaryConsumer->execute($message));
    }
}
