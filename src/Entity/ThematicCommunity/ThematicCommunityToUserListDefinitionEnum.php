<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\UserListDefinitionEnum;

class ThematicCommunityToUserListDefinitionEnum
{
    public const MAP = [
        'Santé' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_HEALTH,
        'Ecologie' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_ECOLOGY,
        'Ecole' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EDUCATION,
        'Europe' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EUROPE,
        'PME' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_TPE_PME,
        'Agriculture et alimentation' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_AGRICULTURE,
    ];
}
