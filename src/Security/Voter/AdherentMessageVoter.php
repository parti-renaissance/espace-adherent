<?php

namespace AppBundle\Security\Voter;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\Entity\Adherent;

class AdherentMessageVoter extends AbstractAdherentVoter
{
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        switch ($subject) {
            case AdherentMessageTypeEnum::DEPUTY:
                return $adherent->isDeputy();

            case AdherentMessageTypeEnum::REFERENT:
                return $adherent->isReferent();
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return 'CAN_WRITE_ADHERENT_MESSAGE' === $attribute && AdherentMessageTypeEnum::isValid($subject);
    }
}
