<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipOnBoardingInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OnBoardingUtil
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function beginOnBoardingProcess(Adherent $adherent)
    {
        $this->session->set(MembershipOnBoardingInterface::NEW_ADHERENT_ID, $adherent->getId());
    }

    public function hasOnBoardingProcess()
    {
        $this->session->has(MembershipOnBoardingInterface::NEW_ADHERENT_ID);
    }

    public function endOnBoardingProcess()
    {
        $this->session->remove(MembershipOnBoardingInterface::NEW_ADHERENT_ID);
    }
}
