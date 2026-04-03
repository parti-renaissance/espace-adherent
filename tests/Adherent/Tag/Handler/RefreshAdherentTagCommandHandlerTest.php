<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Tag\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adherent\Tag\Handler\RefreshAdherentTagCommandHandler;
use App\Adherent\Tag\TagAggregator;
use App\Entity\Adherent;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\ManagedUsers\Command\RefreshManagedUserProjectionCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class RefreshAdherentTagCommandHandlerTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&AdherentRepository $adherentRepository;
    private MockObject&TagAggregator $tagAggregator;
    private MockObject&MessageBusInterface $bus;
    private RefreshAdherentTagCommandHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->adherentRepository = $this->createMock(AdherentRepository::class);
        $this->tagAggregator = $this->createMock(TagAggregator::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->handler = new RefreshAdherentTagCommandHandler(
            $this->entityManager,
            $this->adherentRepository,
            $this->tagAggregator,
            $this->bus
        );
    }

    public function testHandlerDoesNothingWhenAdherentNotFound(): void
    {
        $uuid = Uuid::uuid4();
        $command = new RefreshAdherentTagCommand($uuid);

        $this->adherentRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with($uuid->toString())
            ->willReturn(null)
        ;

        $this->entityManager->expects(self::never())->method('refresh');
        $this->entityManager->expects(self::never())->method('flush');
        $this->tagAggregator->expects(self::never())->method('getTags');
        $this->bus->expects(self::never())->method('dispatch');

        ($this->handler)($command);
    }

    public function testHandlerDispatchesMessagesInCorrectOrder(): void
    {
        $uuid = Uuid::uuid4();
        $email = 'test@example.org';
        $command = new RefreshAdherentTagCommand($uuid);

        $adherent = $this->createMock(Adherent::class);
        $adherent
            ->method('getUuid')
            ->willReturn($uuid)
        ;
        $adherent
            ->method('getEmailAddress')
            ->willReturn($email)
        ;

        $this->adherentRepository
            ->expects(self::once())
            ->method('findOneByUuid')
            ->with($uuid->toString())
            ->willReturn($adherent)
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('refresh')
            ->with($adherent)
        ;

        $this->tagAggregator
            ->expects(self::once())
            ->method('getTags')
            ->with($adherent)
            ->willReturn(['tag1', 'tag2'])
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $dispatchedMessages = [];
        $this->bus
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessages) {
                $dispatchedMessages[] = $message;

                return new Envelope($message);
            })
        ;

        ($this->handler)($command);

        self::assertCount(2, $dispatchedMessages);

        // First message: RefreshManagedUserProjectionCommand
        self::assertInstanceOf(RefreshManagedUserProjectionCommand::class, $dispatchedMessages[0]);
        self::assertEquals($uuid, $dispatchedMessages[0]->getUuid());

        // Second message: AdherentChangeCommand
        self::assertInstanceOf(AdherentChangeCommand::class, $dispatchedMessages[1]);
        self::assertEquals($uuid, $dispatchedMessages[1]->getUuid());
        self::assertSame($email, $dispatchedMessages[1]->getEmailAddress());
    }
}
