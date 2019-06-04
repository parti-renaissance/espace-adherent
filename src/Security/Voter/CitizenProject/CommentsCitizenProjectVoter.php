<?php

namespace AppBundle\Security\Voter\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommentsCitizenProjectVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $citizenProject)
    {
        return $citizenProject instanceof CitizenProject
            && \in_array($attribute, CitizenProjectPermissions::COMMENTS, true)
        ;
    }

    /**
     * @param string         $attribute
     * @param CitizenProject $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject->isApproved()) {
            return false;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    /**
     * @param CitizenProject $citizenProject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $citizenProject): bool
    {
        return (bool) $adherent->getCitizenProjectMembershipFor($citizenProject);
    }
}
