<?php

declare(strict_types=1);

namespace Tests\App\AppSession\Handler;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\AppSession\Command\UpdateAdherentLastLoginCommand;
use App\AppSession\Handler\UpdateAdherentLastLoginCommandHandler;
use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class UpdateAdherentLastLoginCommandHandlerTest extends TestCase
{
    public function testFirstLoginOfSignupAccountDispatchesTagRefresh(): void
    {
        $uuid = Uuid::v4();

        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        $adherent->method('getLastLoggedAt')->willReturn(null);
        $adherent->method('getUuid')->willReturn($uuid);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (object $command) use ($uuid): bool {
                return $command instanceof AsyncRefreshAdherentTagCommand && $command->getUuid()->equals($uuid);
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $this->handle($uuid, $adherent, $bus);
    }

    public function testFirstLoginOfNonSignupAccountDoesNotDispatch(): void
    {
        $uuid = Uuid::v4();

        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = false;
        $adherent->method('getLastLoggedAt')->willReturn(null);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handle($uuid, $adherent, $bus);
    }

    public function testSubsequentLoginDoesNotDispatch(): void
    {
        $uuid = Uuid::v4();

        $adherent = $this->createStub(Adherent::class);
        $adherent->signupAccount = true;
        // lastLoggedAt already set: not a first login → no refresh.
        $adherent->method('getLastLoggedAt')->willReturn(new \DateTime('2026-01-01'));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $this->handle($uuid, $adherent, $bus);
    }

    private function handle(Uuid $uuid, Adherent $adherent, MessageBusInterface $bus): void
    {
        $repository = $this->createMock(AdherentRepository::class);
        $repository->expects(self::once())->method('findOneByUuid')->with($uuid)->willReturn($adherent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        (new UpdateAdherentLastLoginCommandHandler($repository, $entityManager, $bus))(
            new UpdateAdherentLastLoginCommand($uuid)
        );
    }
}
