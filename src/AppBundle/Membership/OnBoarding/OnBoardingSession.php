<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OnBoardingSession
{
    const NEW_ADHERENT = 'membership.on_boarding.new_adherent';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function start(Adherent $adherent): void
    {
        $this->session->set(self::NEW_ADHERENT, $adherent->getId());
    }

    public function isStarted(): bool
    {
        return $this->session->has(self::NEW_ADHERENT);
    }

    public function terminate(): void
    {
        $this->session->remove(self::NEW_ADHERENT);
    }
}
