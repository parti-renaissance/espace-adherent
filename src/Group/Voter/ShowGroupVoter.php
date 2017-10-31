<?php

namespace AppBundle\Group\Voter;

use AppBundle\Group\GroupPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShowGroupVoter extends AbstractGroupVoter
{
    protected function supports($attribute, $group)
    {
        return GroupPermissions::SHOW === $attribute && $group instanceof Group;
    }

    protected function voteOnAttribute($attribute, $group, TokenInterface $token)
    {
        if ($group->isApproved()) {
            return true;
        }

        return parent::voteOnAttribute($attribute, $group, $token);
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Group $group): bool
    {
        return $group->isCreatedBy($adherent->getUuid());
    }
}
