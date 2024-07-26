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

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE === $attribute;
    }
}
