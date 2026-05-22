<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Synchronisation\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommandInterface;
use App\Mailchimp\Synchronisation\Handler\AdherentChangeCommandHandler;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class AdherentChangeCommandHandlerTest extends TestCase
{
    private Manager&MockObject $manager;
    private AdherentRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $entityManager;
    private AdherentChangeCommandHandler $handler;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(Manager::class);
        $this->repository = $this->createMock(AdherentRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new AdherentChangeCommandHandler(
            $this->manager,
            $this->repository,
            $this->entityManager,
        );
    }

    public function testInvokeOnUnknownAdherentReturnsEarly(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn(null)
        ;

        $this->entityManager->expects($this->never())->method('refresh');
        $this->manager->expects($this->never())->method('editMember');
        $this->entityManager->expects($this->never())->method('flush');

        ($this->handler)($this->createMessage());
    }

    public function testInvokeOnPendingAdherentSkipsSync(): void
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('isPending')->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($adherent)
        ;

        $this->entityManager->expects($this->never())->method('refresh');
        $this->manager->expects($this->never())->method('editMember');
        $this->entityManager->expects($this->never())->method('flush');

        ($this->handler)($this->createMessage());
    }

    public function testInvokeOnToDeleteAdherentSkipsSync(): void
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('isPending')->willReturn(false);
        $adherent->expects($this->once())->method('isToDelete')->willReturn(true);

        $this->repository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($adherent)
        ;

        // Guard must short-circuit before any write: a concurrent hard-delete of the adherent
        // would otherwise make the flush fail with a foreign key violation on the
        // adherent_subscription_type join table.
        $this->entityManager->expects($this->never())->method('refresh');
        $this->manager->expects($this->never())->method('editMember');
        $this->entityManager->expects($this->never())->method('flush');

        ($this->handler)($this->createMessage());
    }

    public function testInvokeOnEnabledAdherentSyncsAndFlushes(): void
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('isPending')->willReturn(false);
        $adherent->expects($this->once())->method('isToDelete')->willReturn(false);

        $message = $this->createMessage();

        $this->repository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($adherent)
        ;

        $this->entityManager
            ->expects($this->once())
            ->method('refresh')
            ->with($this->identicalTo($adherent))
        ;
        $this->manager
            ->expects($this->once())
            ->method('editMember')
            ->with($this->identicalTo($adherent), $this->identicalTo($message))
        ;
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('clear');

        ($this->handler)($message);
    }

    private function createMessage(): AdherentChangeCommandInterface&Stub
    {
        $message = $this->createStub(AdherentChangeCommandInterface::class);
        $message->method('getUuid')->willReturn(Uuid::v4());

        return $message;
    }
}
