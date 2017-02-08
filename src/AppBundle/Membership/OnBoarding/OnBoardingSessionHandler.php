<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OnBoardingSessionHandler
{
    const NEW_ADHERENT = 'membership.on_boarding.new_adherent';

    public function start(SessionInterface $session, Adherent $adherent): void
    {
        $session->set(self::NEW_ADHERENT, $adherent->getId());
    }

    public function isStarted(SessionInterface $session): bool
    {
        return $session->has(self::NEW_ADHERENT);
    }

    public function getNewAdherentId(SessionInterface $session): ?int
    {
        return $session->get(self::NEW_ADHERENT);
    }

    public function terminate(SessionInterface $session): void
    {
        $session->remove(self::NEW_ADHERENT);
    }
}
