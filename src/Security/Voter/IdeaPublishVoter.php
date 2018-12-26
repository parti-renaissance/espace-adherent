<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\IdeasWorkshop\IdeaPermissions;

class IdeaPublishVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $subject)
    {
        return IdeaPermissions::PUBLISH === $attribute && $subject instanceof Idea;
    }

    /**
     * @param Idea $idea
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $idea): bool
    {
        return $idea->isPending() && $adherent->getId() === $idea->getAuthor()->getId();
    }
}
