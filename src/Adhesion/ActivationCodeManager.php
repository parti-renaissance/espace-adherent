<?php

declare(strict_types=1);

namespace App\Adhesion;

use App\Adhesion\Exception\ActivationCodeExpiredException;
use App\Adhesion\Exception\ActivationCodeLimitReachedException;
use App\Adhesion\Exception\ActivationCodeNotFoundException;
use App\Adhesion\Exception\ActivationCodeRetryLimitReachedException;
use App\Adhesion\Exception\ActivationCodeRevokedException;
use App\Adhesion\Exception\ActivationCodeUsedException;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentActivationCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActivationCodeManager
{
    public const CODE_TTL = 15; // in minutes
    public const MAX_FAILED_ATTEMPTS = 5;

    public function __construct(
        private readonly AdherentActivationCodeRepository $activationCodeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly RateLimiterFactory $activationAccountRetryLimiter,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function generate(
        Adherent $adherent,
        bool $force = false,
        int $codeLength = 4,
    ): AdherentActivationCode {
        if (!$force) {
            $this->checkAbuse($adherent);
        }

        $this->invalidateForAdherent($adherent);

        $this->entityManager->persist($token = AdherentActivationCode::create($adherent, self::CODE_TTL, $codeLength));
        $this->entityManager->flush();

        return $token;
    }

    public function validate(string $codeValue, Adherent $adherent): void
    {
        $code = $this->checkCode($codeValue, $adherent);

        if (!$adherent->isPending()) {
            return;
        }

        $consumed = $this->entityManager->wrapInTransaction(function () use ($adherent, $code): bool {
            if (0 === $this->activationCodeRepository->markAsUsedIfActive($code)) {
                return false;
            }
            $adherent->enable();
            $this->entityManager->flush();

            return true;
        });

        if (!$consumed) {
            $this->entityManager->refresh($adherent);

            if ($adherent->isPending()) {
                throw new ActivationCodeRevokedException();
            }

            return;
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
    }

    public function checkCode(string $codeValue, Adherent $adherent): AdherentActivationCode
    {
        $limiter = $this->activationAccountRetryLimiter->create('activation_code.validate.'.$adherent->getUuidAsString());

        if (!$limiter->consume()->isAccepted()) {
            throw new ActivationCodeRetryLimitReachedException();
        }

        if (!$code = $this->activationCodeRepository->findOneActiveByCode($codeValue, $adherent)) {
            $this->incrementFailedAttemptsForLatest($adherent);

            throw new ActivationCodeNotFoundException();
        }

        if ($code->isRevoked()) {
            throw new ActivationCodeRevokedException();
        }

        if ($code->isExpired()) {
            throw new ActivationCodeExpiredException();
        }

        if ($code->usedAt) {
            throw new ActivationCodeUsedException();
        }

        return $code;
    }

    public function invalidateForAdherent(Adherent $adherent): void
    {
        $this->activationCodeRepository->invalidateForAdherent($adherent);
    }

    private function incrementFailedAttemptsForLatest(Adherent $adherent): void
    {
        if (!$latest = $this->activationCodeRepository->findLatestActive($adherent)) {
            return;
        }

        $this->activationCodeRepository->incrementFailedAttempts($latest, self::MAX_FAILED_ATTEMPTS);
    }

    private function checkAbuse(Adherent $adherent): void
    {
        $limiter = $this->activationAccountRetryLimiter->create('activation_code.generate.'.$adherent->getUuidAsString());

        if (!$limiter->consume()->isAccepted()) {
            throw new ActivationCodeLimitReachedException();
        }
    }
}
