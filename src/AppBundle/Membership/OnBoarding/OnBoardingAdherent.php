<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipOnBoardingInterface;

/**
 * A simple instance to store a registered adherent not yet activated.
 */
final class OnBoardingAdherent implements MembershipOnBoardingInterface
{
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
