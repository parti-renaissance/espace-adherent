<?php

namespace AppBundle\CitizenInitiative\Voter;

use AppBundle\CitizenInitiative\CitizenInitiativePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizerCitizenInitiativeVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return CitizenInitiativePermissions::MODIFY === $attribute && $subject instanceof CitizenInitiative;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();

        if (!$adherent instanceof Adherent) {
            return false;
        }

        return $subject->getOrganizer() && $adherent->equals($subject->getOrganizer());
    }
}
