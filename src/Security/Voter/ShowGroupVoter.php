<?php

namespace App\Security\Voter;

use App\Committee\CommitteePermissions;
use App\Entity\Adherent;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShowGroupVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $group)
    {
        return CommitteePermissions::SHOW === $attribute && $group instanceof Committee;
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
