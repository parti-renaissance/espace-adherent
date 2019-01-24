<?php

namespace AppBundle\Security\Voter;

use AppBundle\Adherent\AdherentRoleEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentTag;
use AppBundle\Entity\AdherentTagEnum;

class IdeaQGVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return AdherentRoleEnum::QG_IDEAS === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $adherent->getTags()->filter(function (AdherentTag $tag) {
            return AdherentTagEnum::LAREM === $tag->getName();
        })->count() > 0;
    }
}
