<?php

namespace AppBundle\Security\Voter;

use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseGroup;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShowGroupVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $group)
    {
        return CitizenProjectPermissions::SHOW === $attribute && $group instanceof CitizenProject
            || CommitteePermissions::SHOW === $attribute && $group instanceof Committee
        ;
    }

    /**
     * @param string    $attribute
     * @param BaseGroup $group
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $group, TokenInterface $token)
    {
        if ($group->isApproved()) {
            return true;
        }

        return parent::voteOnAttribute($attribute, $group, $token);
    }

    /**
     * @param BaseGroup $group
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $group): bool
    {
        return $group->isCreatedBy($adherent->getUuid());
    }
}
