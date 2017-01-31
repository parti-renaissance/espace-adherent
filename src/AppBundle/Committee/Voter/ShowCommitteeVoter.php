<?php

namespace AppBundle\Committee\Voter;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ShowCommitteeVoter extends AbstractCommitteeVoter
{
    protected function supports($attribute, $committee)
    {
        return CommitteePermissions::SHOW === $attribute && $committee instanceof Committee;
    }

    protected function voteOnAttribute($attribute, $committee, TokenInterface $token)
    {
        if ($committee->isApproved()) {
            return true;
        }

        return parent::voteOnAttribute($attribute, $committee, $token);
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, Committee $committee): bool
    {
        return $committee->isCreatedBy($adherent->getUuid());
    }
}
