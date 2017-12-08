<?php

namespace AppBundle\CitizenAction\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizerCitizenActionVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof CitizenProject
            && in_array($attribute, CitizenActionPermissions::ORGANIZER_PERMS, true);
    }

    /**
     * {@inheritdoc}
     *
     * @param CitizenProject $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        return $user instanceof Adherent && $subject->isApproved() && $subject->isCreatedBy($user->getUuid());
    }
}
