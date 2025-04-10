<?php

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

    public function __construct(
        private readonly AdherentActivationCodeRepository $activationCodeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly RateLimiterFactory $activationAccountRetryLimiter,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function generate(Adherent $adherent, bool $force = false): AdherentActivationCode
    {
        if (!$force) {
            $this->checkAbuse($adherent);
        }

        $this->invalidateForAdherent($adherent);

        $this->entityManager->persist($token = AdherentActivationCode::create($adherent, self::CODE_TTL));
        $this->entityManager->flush();

        return $token;
    }

    public function validate(string $codeValue, Adherent $adherent): void
    {
        $limiter = $this->activationAccountRetryLimiter->create('activation_code.validate.'.$adherent->getUuidAsString());

        if (!$limiter->consume()->isAccepted()) {
            throw new ActivationCodeRetryLimitReachedException();
        }

        if (!$code = $this->activationCodeRepository->findOneByCode($codeValue, $adherent)) {
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

        if ($adherent->isPending()) {
            $adherent->enable();
            $code->usedAt = new \DateTime();
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
    }

    public function invalidateForAdherent(Adherent $adherent): void
    {
        $this->activationCodeRepository->invalidateForAdherent($adherent);
    }

    private function checkAbuse(Adherent $adherent): void
    {
        $limiter = $this->activationAccountRetryLimiter->create('activation_code.generate.'.$adherent->getUuidAsString());

        if (!$limiter->consume()->isAccepted()) {
            throw new ActivationCodeLimitReachedException();
        }
    }
}
