<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;
use App\UserListDefinition\UserListDefinitionPermissions;

class ManageUserListDefinitionsForTypeVoter extends AbstractAdherentVoter
{
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!\in_array($subject, UserListDefinitionEnum::TYPES)) {
            return false;
        }

        if (UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE === $subject && !$adherent->isReferent()) {
            return false;
        }

        return true;
    }

    protected function supports($attribute, $subject)
    {
        return UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE === $attribute;
    }
}
