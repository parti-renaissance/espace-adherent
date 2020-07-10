<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class UserListDefinitionEnum extends Enum
{
    public const TYPE_ELECTED_REPRESENTATIVE = 'elected_representative';
    public const TYPE_ADHERENT = 'adherent';
    public const TYPE_THEMATIC_COMMUNITY = 'thematic_community';
    public const TYPE_LRE = 'lre';

    public const CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM = 'supporting_la_rem';
    public const CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER = 'instances_member';

    public const TYPES = [
        self::TYPE_ELECTED_REPRESENTATIVE,
        self::TYPE_ADHERENT,
        self::TYPE_THEMATIC_COMMUNITY,
        self::TYPE_LRE,
    ];

    public const CHOICES = [
        'user_list_definition.type.elected_representative.label' => self::TYPE_ELECTED_REPRESENTATIVE,
        'user_list_definition.type.adherent.label' => self::TYPE_ADHERENT,
        'user_list_definition.type.thematic_community.label' => self::TYPE_THEMATIC_COMMUNITY,
        'user_list_definition.type.lre.label' => self::TYPE_LRE,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
