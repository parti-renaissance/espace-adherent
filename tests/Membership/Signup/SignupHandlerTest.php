<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Membership\AdherentFactory;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class SignupHandlerTest extends TestCase
{
    public function testRegisterCreatesAdherentWhenEmailUnknown(): void
    {
        $created = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.com')
            ->willReturn(null)
        ;

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

        // No opt-in flags on this command: the subscription handler is still invoked once on create, with false/false.
        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler
            ->expects(self::once())
            ->method('addDefaultTypesToAdherent')
            ->with($created, false, false)
        ;

        $handler = new SignupHandler($em, $this->createStub(ManagerRegistry::class), $adherentRepository, $factory, $subscriptionHandler);

        $result = $handler->register(new SignupCommand('John@Example.com', 'newsletter', 'John'));

        self::assertSame($created, $result);
    }

    public function testRegisterAppliesOptInsOnCreate(): void
    {
        $created = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('opt@example.com')
            ->willReturn(null)
        ;

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

        $handler = new SignupHandler($em, $this->createStub(ManagerRegistry::class), $adherentRepository, $factory, $subscriptionHandler);

        $command = new SignupCommand('opt@example.com', 'newsletter', emailOptIn: true, smsOptIn: true);

        self::assertSame($created, $handler->register($command));
    }

    public function testRegisterDoesNotOverwriteExistingIdentity(): void
    {
        $existing = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('jane@example.com')
            ->willReturn($existing)
        ;

        $factory = $this->createMock(AdherentFactory::class);
        $factory->expects(self::never())->method('createForSignup');

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
        // Only the source row is persisted, never the existing identity.
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(AdherentSignupSource::class));
        $em->expects(self::once())->method('flush');

        // Existing account: opt-ins are create-only, the subscription handler must never run.
        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        $handler = new SignupHandler($em, $this->createStub(ManagerRegistry::class), $adherentRepository, $factory, $subscriptionHandler);

        $result = $handler->register(new SignupCommand('Jane@Example.com', 'petition'));

        self::assertSame($existing, $result);
    }

    public function testSourceLoggedOnceWhenNew(): void
    {
        $existing = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('known@example.com')
            ->willReturn($existing)
        ;

        $sourceRepository = $this->createMock(EntityRepository::class);
        $sourceRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $existing, 'source' => 'event'])
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

        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        $handler = new SignupHandler($em, $this->createStub(ManagerRegistry::class), $adherentRepository, $this->createStub(AdherentFactory::class), $subscriptionHandler);

        self::assertSame($existing, $handler->register(new SignupCommand('known@example.com', 'event')));
    }

    public function testSourceNotDuplicated(): void
    {
        $existing = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('known@example.com')
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
        $em
            ->expects(self::once())
            ->method('getRepository')
            ->with(AdherentSignupSource::class)
            ->willReturn($sourceRepository)
        ;
        // Source already logged: nothing is persisted.
        $em->expects(self::never())->method('persist');
        $em->expects(self::once())->method('flush');

        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        $handler = new SignupHandler($em, $this->createStub(ManagerRegistry::class), $adherentRepository, $this->createStub(AdherentFactory::class), $subscriptionHandler);

        self::assertSame($existing, $handler->register(new SignupCommand('known@example.com', 'event')));
    }

    public function testRegisterHandlesConcurrentCreate(): void
    {
        $created = $this->createStub(Adherent::class);
        $winner = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('race@example.com')
            ->willReturn(null)
        ;

        $factory = $this->createMock(AdherentFactory::class);
        $factory->expects(self::once())->method('createForSignup')->willReturn($created);

        // First EM: source missing, then flush collides on the unique email.
        $sourceRepository1 = $this->createMock(EntityRepository::class);
        $sourceRepository1
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $created, 'source' => 'event'])
            ->willReturn(null)
        ;

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

        // Fresh EM after reset: re-read the winner, log the still-missing source, flush cleanly.
        $adherentRepository2 = $this->createMock(EntityRepository::class);
        $adherentRepository2
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['emailAddress' => 'race@example.com'])
            ->willReturn($winner)
        ;

        $sourceRepository2 = $this->createMock(EntityRepository::class);
        $sourceRepository2
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['adherent' => $winner, 'source' => 'event'])
            ->willReturn(null)
        ;

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

        // The winner is an existing account reloaded on a fresh EM: opt-ins must not run on this branch.
        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        $handler = new SignupHandler($em, $registry, $adherentRepository, $factory, $subscriptionHandler);

        $result = $handler->register(new SignupCommand('race@example.com', 'event'));

        self::assertSame($winner, $result);
    }

    public function testRegisterIsIdempotentWhenConcurrentSourceInsertWins(): void
    {
        // Email create collides (UCV), then on the recovery branch a concurrent request inserts the same
        // (adherent, source) pair first, so the recovery flush also throws UCV. It must be swallowed: the
        // source is already recorded and the endpoint stays idempotent (winner returned, no exception).
        $created = $this->createStub(Adherent::class);
        $winner = $this->createStub(Adherent::class);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByEmail')->with('race@example.com')->willReturn(null);

        $factory = $this->createMock(AdherentFactory::class);
        $factory->expects(self::once())->method('createForSignup')->willReturn($created);

        $sourceRepository1 = $this->createMock(EntityRepository::class);
        $sourceRepository1->expects(self::once())->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('getRepository')->with(AdherentSignupSource::class)->willReturn($sourceRepository1);
        $em->expects(self::exactly(2))->method('persist');
        $em->expects(self::once())->method('flush')->willThrowException($this->createStub(UniqueConstraintViolationException::class));

        $adherentRepository2 = $this->createMock(EntityRepository::class);
        $adherentRepository2->expects(self::once())->method('findOneBy')->with(['emailAddress' => 'race@example.com'])->willReturn($winner);

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
        // The recovery flush collides on the (adherent, source) unique key — must be swallowed.
        $freshManager->expects(self::once())->method('flush')->willThrowException($this->createStub(UniqueConstraintViolationException::class));

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('resetManager')->willReturn($freshManager);

        $subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $subscriptionHandler->expects(self::never())->method('addDefaultTypesToAdherent');

        $handler = new SignupHandler($em, $registry, $adherentRepository, $factory, $subscriptionHandler);

        self::assertSame($winner, $handler->register(new SignupCommand('race@example.com', 'event')));
    }
}
