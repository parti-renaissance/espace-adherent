<?php

namespace AppBundle\MoocEvent\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\MoocEvent\MoocEventPermissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class OrganizerMoocEventVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $subject, array $attributes)
    {
        $adherent = $token->getUser();
        if (null === $subject || !$adherent instanceof Adherent) {
            return self::ACCESS_ABSTAIN;
        }

        if (!in_array(MoocEventPermissions::MODIFY, $attributes, true)) {
            return self::ACCESS_ABSTAIN;
        }

        return $subject->getOrganizer() && $adherent->equals($subject->getOrganizer()) ? self::ACCESS_GRANTED : self::ACCESS_ABSTAIN;
    }
}
