<?php

declare(strict_types=1);

namespace Tests\App\Adhesion;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\Exception\ActivationCodeExpiredException;
use App\Adhesion\Exception\ActivationCodeLimitReachedException;
use App\Adhesion\Exception\ActivationCodeNotFoundException;
use App\Adhesion\Exception\ActivationCodeRetryLimitReachedException;
use App\Adhesion\Exception\ActivationCodeRevokedException;
use App\Adhesion\Exception\ActivationCodeUsedException;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentActivationCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActivationCodeManagerTest extends TestCase
{
    public function testGenerateUsesProvidedLength(): void
    {
        $adherent = $this->createAdherent();

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('invalidateForAdherent')
            ->with($adherent)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn (AdherentActivationCode $code) => 1 === preg_match('/^\d{3}$/', $code->value)))
        ;
        $em->expects(self::once())->method('flush');

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $token = $manager->generate(
            $adherent,
            force: true,
            codeLength: 3,
        );

        self::assertMatchesRegularExpression('/^\d{3}$/', $token->value);
    }

    public function testGenerateChecksAbuseWhenNotForced(): void
    {
        $adherent = $this->createAdherent();

        $manager = new ActivationCodeManager(
            $this->createStub(AdherentActivationCodeRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createSaturatedLimiter(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->expectException(ActivationCodeLimitReachedException::class);

        $manager->generate($adherent);
    }

    public function testValidateRejectsWhenRetryLimitReached(): void
    {
        $adherent = $this->createAdherent();

        $manager = new ActivationCodeManager(
            $this->createStub(AdherentActivationCodeRepository::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createSaturatedLimiter(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->expectException(ActivationCodeRetryLimitReachedException::class);

        $manager->validate('123', $adherent);
    }

    public function testValidateDelegatesAtomicIncrementOnFailedLookup(): void
    {
        // Lockout under concurrency: the manager must NOT mutate in-memory and flush — it must call
        // the repository's atomic UPDATE so parallel failed attempts cannot lose increments.
        $adherent = $this->createAdherent();

        $latest = AdherentActivationCode::create($adherent, 10, 3);

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('findOneActiveByCode')
            ->with('999', $adherent)
            ->willReturn(null)
        ;
        $repo
            ->expects(self::once())
            ->method('findLatestActive')
            ->with($adherent)
            ->willReturn($latest)
        ;
        $repo
            ->expects(self::once())
            ->method('incrementFailedAttempts')
            ->with($latest, ActivationCodeManager::MAX_FAILED_ATTEMPTS)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        // No in-memory mutation + flush: the increment is delegated to the repo's atomic SQL UPDATE.
        $em->expects(self::never())->method('flush');

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $this->createStub(EventDispatcherInterface::class),
        );

        $this->expectException(ActivationCodeNotFoundException::class);

        $manager->validate('999', $adherent);
    }

    public function testValidateOnPendingAdherentEnablesAndDispatchesUserValidated(): void
    {
        $adherent = $this->createAdherent();
        self::assertTrue($adherent->isPending(), 'fixture adherent must start PENDING');

        $code = AdherentActivationCode::create($adherent, 10, 3);

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('findOneActiveByCode')
            ->with($code->value, $adherent)
            ->willReturn($code)
        ;
        // Atomic compare-and-set: 1 affected row → the current request is the one that consumed the code.
        $repo
            ->expects(self::once())
            ->method('markAsUsedIfActive')
            ->with($code)
            ->willReturn(1)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        // Activation runs inside a single Doctrine transaction: pass-through the callable so the
        // enclosed flush + dispatch contract stays verifiable by the mock.
        $em
            ->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $cb) => $cb($em))
        ;

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static fn (UserEvent $event) => $event->getAdherent() === $adherent),
                UserEvents::USER_VALIDATED,
            )
        ;

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $dispatcher,
        );

        $manager->validate($code->value, $adherent);

        self::assertTrue($adherent->isEnabled());
    }

    public function testValidateConcurrentActivateThatEnabledTheAccountExitsSilently(): void
    {
        // Race-safe consume: when the atomic UPDATE returns 0 affected rows AND the parallel call
        // was an activate() that enabled the adherent, the current request must NOT re-enable nor
        // re-dispatch USER_VALIDATED. We reload the adherent to confirm the transition happened.
        $adherent = $this->createAdherent();
        $code = AdherentActivationCode::create($adherent, 10, 3);

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('findOneActiveByCode')
            ->with($code->value, $adherent)
            ->willReturn($code)
        ;
        $repo
            ->expects(self::once())
            ->method('markAsUsedIfActive')
            ->with($code)
            ->willReturn(0)
        ;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $em
            ->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $cb) => $cb($em))
        ;
        // Simulate that the concurrent transaction already enabled the adherent: refresh() observes it.
        $em
            ->expects(self::once())
            ->method('refresh')
            ->with($adherent)
            ->willReturnCallback(static function (Adherent $a): void {
                $a->enable();
            })
        ;

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $dispatcher,
        );

        // No exception expected: the account is already in the target state.
        $manager->validate($code->value, $adherent);
    }

    public function testValidateThrowsRevokedWhenConcurrentResendInvalidatedTheCode(): void
    {
        // Race scenario: a parallel /resend-code revoked the current code (invalidateForAdherent)
        // between checkCode() and the CAS. CAS returns 0 but the adherent is still PENDING. The
        // manager must surface the failure so the controller can return a uniform error, instead
        // of silently 204-ing the user into thinking activation succeeded.
        $adherent = $this->createAdherent();
        self::assertTrue($adherent->isPending(), 'fixture must start PENDING');

        $code = AdherentActivationCode::create($adherent, 10, 3);

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('findOneActiveByCode')
            ->with($code->value, $adherent)
            ->willReturn($code)
        ;
        $repo->expects(self::once())->method('markAsUsedIfActive')->with($code)->willReturn(0);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $em
            ->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $cb) => $cb($em))
        ;
        // refresh() is a no-op here: the parallel call did NOT enable the adherent (it only revoked
        // the code). The adherent stays PENDING.
        $em->expects(self::once())->method('refresh')->with($adherent);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $dispatcher,
        );

        $this->expectException(ActivationCodeRevokedException::class);

        $manager->validate($code->value, $adherent);
    }

    public function testValidateOnEnabledAdherentDoesNotDispatchUserValidated(): void
    {
        $adherent = $this->createAdherent();
        $adherent->enable();
        self::assertTrue($adherent->isEnabled(), 'fixture adherent must be ENABLED for this scenario');

        $code = AdherentActivationCode::create($adherent, 10, 3);

        $repo = $this->createMock(AdherentActivationCodeRepository::class);
        $repo
            ->expects(self::once())
            ->method('findOneActiveByCode')
            ->with($code->value, $adherent)
            ->willReturn($code)
        ;
        // Already-enabled accounts short-circuit BEFORE the atomic consume — checkCode succeeds
        // (proof of possession works) but no state transition happens.
        $repo->expects(self::never())->method('markAsUsedIfActive');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');

        $manager = new ActivationCodeManager(
            $repo,
            $em,
            $this->createAcceptingLimiter(),
            $dispatcher,
        );

        $manager->validate($code->value, $adherent);

        self::assertNull($code->usedAt, 'usedAt must stay null when no PENDING transition happens');
    }

    public function testValidateThrowsRevokedExceptionWhenCodeRevokedRaceCondition(): void
    {
        $adherent = $this->createAdherent();
        $code = AdherentActivationCode::create($adherent, 10, 3);
        $code->revokedAt = new \DateTime();

        $manager = $this->createManagerReturning($code);

        $this->expectException(ActivationCodeRevokedException::class);

        $manager->validate($code->value, $adherent);
    }

    public function testValidateThrowsExpiredExceptionWhenCodePastTtl(): void
    {
        $adherent = $this->createAdherent();
        $code = AdherentActivationCode::create($adherent, -1, 3);

        $manager = $this->createManagerReturning($code);

        $this->expectException(ActivationCodeExpiredException::class);

        $manager->validate($code->value, $adherent);
    }

    public function testValidateThrowsUsedExceptionWhenCodeAlreadyConsumed(): void
    {
        $adherent = $this->createAdherent();
        $code = AdherentActivationCode::create($adherent, 10, 3);
        $code->usedAt = new \DateTime();

        $manager = $this->createManagerReturning($code);

        $this->expectException(ActivationCodeUsedException::class);

        $manager->validate($code->value, $adherent);
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('jane.doe@example.org'),
            'ABC-100',
            'jane.doe@example.org',
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
    }

    private function createAcceptingLimiter(): RateLimiterFactory
    {
        // Real factory backed by in-memory storage so tests cover the same call surface as production.
        // A very high limit ensures the limiter never rejects within a single test scenario.
        return new RateLimiterFactory(
            [
                'id' => 'test_accepting',
                'policy' => 'sliding_window',
                'limit' => 1_000_000,
                'interval' => '1 minute',
            ],
            new InMemoryStorage(),
        );
    }

    private function createSaturatedLimiter(): RateLimiterFactory
    {
        $factory = new RateLimiterFactory(
            [
                'id' => 'test_saturated',
                'policy' => 'sliding_window',
                'limit' => 1,
                'interval' => '1 minute',
            ],
            new InMemoryStorage(),
        );

        // Pre-exhaust both candidate keys used by the manager (generate.{uuid} and validate.{uuid}).
        $uuid = $this->createAdherent()->getUuidAsString();
        $factory->create('activation_code.generate.'.$uuid)->consume();
        $factory->create('activation_code.validate.'.$uuid)->consume();

        return $factory;
    }

    private function createManagerReturning(AdherentActivationCode $code): ActivationCodeManager
    {
        $repo = $this->createStub(AdherentActivationCodeRepository::class);
        $repo->method('findOneActiveByCode')->willReturn($code);

        return new ActivationCodeManager(
            $repo,
            $this->createStub(EntityManagerInterface::class),
            $this->createAcceptingLimiter(),
            $this->createStub(EventDispatcherInterface::class),
        );
    }
}
