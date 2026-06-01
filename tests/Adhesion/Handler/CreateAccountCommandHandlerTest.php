<?php

declare(strict_types=1);

namespace Tests\App\Adhesion\Handler;

use App\Address\Address;
use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Adhesion\Handler\CreateAccountCommandHandler;
use App\Adhesion\Request\MembershipRequest;
use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Entity\SignupSource;
use App\Membership\AdherentFactory;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Repository\AdherentSignupSourceRepository;
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

    public function testCreatesAccountRecordsRenaissanceSignupSource(): void
    {
        $request = new MembershipRequest();
        $request->email = 'new@example.com';
        $request->exclusiveMembership = true;
        $request->allowNotifications = false;
        $request->acceptSmsNotification = false;
        $request->address = $this->createConfiguredStub(Address::class, [
            'isFrenchAddress' => true,
            'getCity' => '75008-75108',
        ]);

        $adherent = $this->createConfiguredStub(Adherent::class, ['isEligibleForMembershipPayment' => false]);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('new@example.com')
            ->willReturn(null);

        $adherentFactory = $this->createMock(AdherentFactory::class);
        $adherentFactory
            ->expects(self::once())
            ->method('createFromRenaissanceMembershipRequest')
            ->with($request)
            ->willReturn($adherent);

        $adherentSignupSourceRepository = $this->createMock(AdherentSignupSourceRepository::class);
        $adherentSignupSourceRepository
            ->expects(self::once())
            ->method('existsFor')
            ->with($adherent, SignupSource::CODE_RENAISSANCE)
            ->willReturn(false);

        $persisted = [];
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::atLeast(2))
            ->method('persist')
            ->willReturnCallback(static function (object $entity) use (&$persisted): void {
                $persisted[] = $entity;
            });
        $entityManager->expects(self::once())->method('flush');

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::never())->method('sendConnexionDetailsMessage');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(UserEvent::class), UserEvents::USER_CREATED)
            ->willReturnArgument(0);

        $handler = new CreateAccountCommandHandler($entityManager, $adherentRepository, $adherentSignupSourceRepository, $adherentFactory, $notifier, $eventDispatcher);
        $handler(new CreateAccountCommand($request));

        $signupSources = array_values(array_filter($persisted, static fn (object $entity): bool => $entity instanceof AdherentSignupSource));
        self::assertCount(1, $signupSources, 'A single AdherentSignupSource must be recorded.');
        self::assertSame(SignupSource::CODE_RENAISSANCE, $signupSources[0]->source);
        self::assertSame($adherent, $signupSources[0]->adherent);
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

        $adherentSignupSourceRepository = $this->createMock(AdherentSignupSourceRepository::class);
        $adherentSignupSourceRepository->expects(self::never())->method('existsFor');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $handler = new CreateAccountCommandHandler($entityManager, $adherentRepository, $adherentSignupSourceRepository, $adherentFactory, $notifier, $eventDispatcher);

        return $handler(new CreateAccountCommand($request));
    }

    private static function assertResultIsAlreadyExists(CreateAdherentResult $result): void
    {
        self::assertFalse($result->isNextStepPayment());
        self::assertFalse($result->isNextStepActivation());
        self::assertNull($result->getAdherent());
    }
}
