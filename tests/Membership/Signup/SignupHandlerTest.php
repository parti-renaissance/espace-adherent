<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\SignupExcludedAdherentMessage;
use App\Membership\AdherentFactory;
use App\Membership\MembershipNotifier;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Repository\AdherentRepository;
use App\Repository\BannedAdherentRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SignupHandlerTest extends TestCase
{
    public function testHandleBannedEmailSendsExcludedMessage(): void
    {
        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo
            ->expects(self::once())
            ->method('countForEmail')
            ->with('banned@example.com')
            ->willReturn(1)
        ;

        $mailer = $this->createMock(MailerService::class);
        $mailer
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::isInstanceOf(SignupExcludedAdherentMessage::class))
            ->willReturn(true)
        ;

        // Adherent lookup must not happen once the email is identified as banned.
        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo->expects(self::never())->method('findOneByEmail');
        $adherentRepo->expects(self::never())->method('findOneByEmailAndStatus');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::never())->method('sendConnexionDetailsMessage');

        $handler = $this->createHandler([
            'em' => $em,
            'adherentRepo' => $adherentRepo,
            'bannedRepo' => $bannedRepo,
            'notifier' => $notifier,
            'mailer' => $mailer,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('Banned@Example.com', 'newsletter'));
    }

    public function testHandleExistingActiveSendsConnexionDetails(): void
    {
        $existing = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->with('jane@example.com')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo
            ->expects(self::once())
            ->method('findOneByEmailAndStatus')
            ->with('jane@example.com', [Adherent::PENDING, Adherent::ENABLED])
            ->willReturn($existing)
        ;
        // No need to query findOneByEmail when the active lookup already returned a hit.
        $adherentRepo->expects(self::never())->method('findOneByEmail');

        $sourceRepository = $this->createMock(EntityRepository::class);
        $sourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $existing, 'source' => 'petition'])
            ->willReturn(null)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects(self::once())
            ->method('getRepository')
            ->with(AdherentSignupSource::class)
            ->willReturn($sourceRepository)
        ;
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(AdherentSignupSource::class));
        $em->expects(self::once())->method('flush');

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier
            ->expects(self::once())
            ->method('sendConnexionDetailsMessage')
            ->with($existing)
        ;

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->createHandler([
            'em' => $em,
            'adherentRepo' => $adherentRepo,
            'bannedRepo' => $bannedRepo,
            'notifier' => $notifier,
            'mailer' => $mailer,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('Jane@Example.com', 'petition'));
    }

    public function testHandleExistingActiveSkipsSourceLogWhenAlreadyRecorded(): void
    {
        $existing = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->with('known@example.com')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo
            ->expects(self::once())
            ->method('findOneByEmailAndStatus')
            ->with('known@example.com', [Adherent::PENDING, Adherent::ENABLED])
            ->willReturn($existing)
        ;

        $sourceRepository = $this->createMock(EntityRepository::class);
        $sourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $existing, 'source' => 'event'])
            ->willReturn($this->createStub(AdherentSignupSource::class))
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getRepository')->with(AdherentSignupSource::class)->willReturn($sourceRepository);
        // Source already logged: nothing is persisted; flush is still issued so the read transaction closes cleanly.
        $em->expects(self::never())->method('persist');
        $em->expects(self::once())->method('flush');

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::once())->method('sendConnexionDetailsMessage')->with($existing);

        $handler = $this->createHandler([
            'em' => $em,
            'adherentRepo' => $adherentRepo,
            'bannedRepo' => $bannedRepo,
            'notifier' => $notifier,
        ]);

        $handler->handle(new SignupCommand('known@example.com', 'event'));
    }

    public function testHandleInactiveExistingAdherentRemainsSilent(): void
    {
        // Both DISABLED (deactivated account) and TO_DELETE (deletion in progress) must stay silent:
        // a 201 with no side effect, to avoid leaking account state to an enumerating client.
        $inactive = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->with('inactive@example.com')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo
            ->expects(self::once())
            ->method('findOneByEmailAndStatus')
            ->with('inactive@example.com', [Adherent::PENDING, Adherent::ENABLED])
            ->willReturn(null)
        ;
        $adherentRepo
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('inactive@example.com')
            ->willReturn($inactive)
        ;

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');
        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::never())->method('sendConnexionDetailsMessage');
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->createHandler([
            'adherentRepo' => $adherentRepo,
            'bannedRepo' => $bannedRepo,
            'notifier' => $notifier,
            'mailer' => $mailer,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('inactive@example.com', 'newsletter'));
    }

    public function testHandleNewEmailCreatesAdherentAndDispatchesConfirmation(): void
    {
        $created = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->with('john@example.com')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo->expects(self::once())->method('findOneByEmailAndStatus')->willReturn(null);
        $adherentRepo->expects(self::once())->method('findOneByEmail')->with('john@example.com')->willReturn(null);

        $factory = $this->createMock(AdherentFactory::class);
        $factory
            ->expects(self::once())
            ->method('createForSignup')
            ->with('john@example.com', null, 'John', null, null, null)
            ->willReturn($created)
        ;

        $sourceRepository = $this->createMock(EntityRepository::class);
        $sourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $created, 'source' => 'newsletter'])
            ->willReturn(null)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects(self::once())
            ->method('getRepository')
            ->with(AdherentSignupSource::class)
            ->willReturn($sourceRepository)
        ;
        $em
            ->expects(self::exactly(2))
            ->method('persist')
            ->with(self::logicalOr(
                self::identicalTo($created),
                self::isInstanceOf(AdherentSignupSource::class)
            ))
        ;
        $em->expects(self::once())->method('flush');

        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler
            ->expects(self::once())
            ->method('addDefaultTypesToAdherent')
            ->with($created, false, false)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (SendSignupConfirmationCommand $command) use ($created): bool {
                return $command->adherent === $created;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $notifier = $this->createMock(MembershipNotifier::class);
        $notifier->expects(self::never())->method('sendConnexionDetailsMessage');

        $handler = $this->createHandler([
            'em' => $em,
            'adherentRepo' => $adherentRepo,
            'factory' => $factory,
            'bannedRepo' => $bannedRepo,
            'subscriptionHandler' => $subscriptionHandler,
            'notifier' => $notifier,
            'mailer' => $mailer,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('John@Example.com', 'newsletter', 'John'));
    }

    public function testHandleNewEmailWithOptInsRegistersSubscriptions(): void
    {
        $created = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo->expects(self::once())->method('findOneByEmailAndStatus')->willReturn(null);
        $adherentRepo->expects(self::once())->method('findOneByEmail')->willReturn(null);

        $factory = $this->createMock(AdherentFactory::class);
        $factory->expects(self::once())->method('createForSignup')->willReturn($created);

        $sourceRepository = $this->createMock(EntityRepository::class);
        $sourceRepository->expects(self::once())->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getRepository')->with(AdherentSignupSource::class)->willReturn($sourceRepository);
        $em->expects(self::exactly(2))->method('persist');
        $em->expects(self::once())->method('flush');

        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler
            ->expects(self::once())
            ->method('addDefaultTypesToAdherent')
            ->with($created, true, true)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(SendSignupConfirmationCommand::class))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $handler = $this->createHandler([
            'em' => $em,
            'adherentRepo' => $adherentRepo,
            'factory' => $factory,
            'bannedRepo' => $bannedRepo,
            'subscriptionHandler' => $subscriptionHandler,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('opt@example.com', 'newsletter', emailOptIn: true, smsOptIn: true));
    }

    public function testHandleNewEmailConcurrentCreateRecoversAndDispatchesConfirmation(): void
    {
        $created = $this->createStub(Adherent::class);
        $winner = $this->createStub(Adherent::class);

        $bannedRepo = $this->createMock(BannedAdherentRepository::class);
        $bannedRepo->expects(self::once())->method('countForEmail')->willReturn(0);

        $adherentRepo = $this->createMock(AdherentRepository::class);
        $adherentRepo->expects(self::once())->method('findOneByEmailAndStatus')->willReturn(null);
        $adherentRepo->expects(self::once())->method('findOneByEmail')->willReturn(null);

        $factory = $this->createMock(AdherentFactory::class);
        $factory->expects(self::once())->method('createForSignup')->willReturn($created);

        $sourceRepository1 = $this->createMock(EntityRepository::class);
        $sourceRepository1->expects(self::once())->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects(self::once())
            ->method('getRepository')
            ->with(AdherentSignupSource::class)
            ->willReturn($sourceRepository1)
        ;
        $em->expects(self::exactly(2))->method('persist');
        $em
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($this->createStub(UniqueConstraintViolationException::class))
        ;

        // Fresh EM after reset: re-read the winner from the unique-email constraint, log source, flush.
        $adherentRepository2 = $this->createMock(EntityRepository::class);
        $adherentRepository2
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['emailAddress' => 'race@example.com'])
            ->willReturn($winner)
        ;

        $sourceRepository2 = $this->createMock(EntityRepository::class);
        $sourceRepository2->expects(self::once())->method('findOneBy')->willReturn(null);

        $freshManager = $this->createMock(ObjectManager::class);
        $freshManager
            ->expects(self::atLeastOnce())
            ->method('getRepository')
            ->willReturnMap([
                [Adherent::class, $adherentRepository2],
                [AdherentSignupSource::class, $sourceRepository2],
            ])
        ;
        $freshManager->expects(self::once())->method('persist')->with(self::isInstanceOf(AdherentSignupSource::class));
        $freshManager->expects(self::once())->method('flush');

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('resetManager')->willReturn($freshManager);

        // The winner is an existing account reloaded on a fresh EM: subscriptions must not be re-applied.
        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        // The confirmation is still dispatched on the winner: idempotent retry covers a lost race.
        $bus = $this->createMock(MessageBusInterface::class);
        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (SendSignupConfirmationCommand $command) use ($winner): bool {
                return $command->adherent === $winner;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $handler = $this->createHandler([
            'em' => $em,
            'registry' => $registry,
            'adherentRepo' => $adherentRepo,
            'factory' => $factory,
            'bannedRepo' => $bannedRepo,
            'subscriptionHandler' => $subscriptionHandler,
            'bus' => $bus,
        ]);

        $handler->handle(new SignupCommand('race@example.com', 'event'));
    }

    /**
     * Build the handler with default no-op mocks for any dependency not explicitly overridden.
     * Keeps each test focused on the collaborators that are actually exercised.
     */
    private function createHandler(array $overrides = []): SignupHandler
    {
        return new SignupHandler(
            $overrides['em'] ?? $this->createStub(EntityManagerInterface::class),
            $overrides['registry'] ?? $this->createStub(ManagerRegistry::class),
            $overrides['adherentRepo'] ?? $this->createStub(AdherentRepository::class),
            $overrides['bannedRepo'] ?? $this->createStub(BannedAdherentRepository::class),
            $overrides['factory'] ?? $this->createStub(AdherentFactory::class),
            $overrides['subscriptionHandler'] ?? $this->createStub(SubscriptionHandler::class),
            $overrides['notifier'] ?? $this->createStub(MembershipNotifier::class),
            $overrides['mailer'] ?? $this->createStub(MailerService::class),
            $overrides['bus'] ?? $this->createStub(MessageBusInterface::class),
        );
    }
}
