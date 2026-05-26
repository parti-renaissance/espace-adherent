<?php

declare(strict_types=1);

namespace Tests\App\Adhesion\Handler;

use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Adhesion\Handler\CreateAccountCommandHandler;
use App\Adhesion\Request\MembershipRequest;
use App\Entity\Adherent;
use App\Membership\AdherentFactory;
use App\Membership\MembershipNotifier;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CreateAccountCommandHandlerTest extends TestCase
{
    public function testAnonymousAdhesionOnPendingEmailSendsConnexionDetails(): void
    {
        $adherent = $this->createConfiguredStub(Adherent::class, ['isEnabled' => false, 'isPending' => true]);

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::once())->method('sendConnexionDetailsMessage')->with($adherent);

        self::assertResultIsAlreadyExists($this->handleAnonymousAdhesion($adherent, $notifier));
    }

    public function testAnonymousAdhesionOnEnabledEmailStillSendsConnexionDetails(): void
    {
        $adherent = $this->createConfiguredStub(Adherent::class, ['isEnabled' => true, 'isPending' => false]);

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::once())->method('sendConnexionDetailsMessage')->with($adherent);

        self::assertResultIsAlreadyExists($this->handleAnonymousAdhesion($adherent, $notifier));
    }

    public function testAnonymousAdhesionOnDisabledEmailDoesNotSendConnexionDetails(): void
    {
        $adherent = $this->createConfiguredStub(Adherent::class, ['isEnabled' => false, 'isPending' => false]);

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::never())->method('sendConnexionDetailsMessage');

        self::assertResultIsAlreadyExists($this->handleAnonymousAdhesion($adherent, $notifier));
    }

    private function handleAnonymousAdhesion(Adherent $existing, MembershipNotifier $notifier): CreateAdherentResult
    {
        $request = new MembershipRequest();
        $request->email = 'captured@example.com';

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('captured@example.com')
            ->willReturn($existing)
        ;

        // The AlreadyExists branch must not touch the member-creation flow.
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $adherentFactory = $this->createMock(AdherentFactory::class);
        $adherentFactory->expects(self::never())->method('createFromRenaissanceMembershipRequest');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $handler = new CreateAccountCommandHandler($entityManager, $adherentRepository, $adherentFactory, $notifier, $eventDispatcher);

        return $handler(new CreateAccountCommand($request));
    }

    private static function assertResultIsAlreadyExists(CreateAdherentResult $result): void
    {
        self::assertFalse($result->isNextStepPayment());
        self::assertFalse($result->isNextStepActivation());
        self::assertNull($result->getAdherent());
    }
}
