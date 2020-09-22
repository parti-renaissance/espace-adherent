<?php

namespace App\Entity\ThematicCommunity;

use App\Entity\UserListDefinitionEnum;

class ThematicCommunityToUserListDefinitionEnum
{
    public const MAP = [
        'Santé' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_HEALTH,
        'Écologie' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_ECOLOGY,
        'Éducation' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EDUCATION,
        'Europe' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_EUROPE,
        'TPE-PME' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_TPE_PME,
        'Agriculture' => UserListDefinitionEnum::TYPE_THEMATIC_COMMUNITY_AGRICULTURE,
    ];
}
