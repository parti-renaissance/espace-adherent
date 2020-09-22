<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class UserListDefinitionEnum extends Enum
{
    public const TYPE_ELECTED_REPRESENTATIVE = 'elected_representative';
    public const TYPE_ADHERENT = 'adherent';
    public const TYPE_THEMATIC_COMMUNITY = 'thematic_community';
    public const TYPE_LRE = 'lre';

    public const TYPE_THEMATIC_COMMUNITY_HEALTH = 'thematic_community_health';
    public const TYPE_THEMATIC_COMMUNITY_EDUCATION = 'thematic_community_education';
    public const TYPE_THEMATIC_COMMUNITY_ECOLOGY = 'thematic_community_ecology';
    public const TYPE_THEMATIC_COMMUNITY_EUROPE = 'thematic_community_europe';
    public const TYPE_THEMATIC_COMMUNITY_TPE_PME = 'thematic_community_tpe_pme';
    public const TYPE_THEMATIC_COMMUNITY_AGRICULTURE = 'thematic_community_agriculture';

    public const CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM = 'supporting_la_rem';
    public const CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER = 'instances_member';

    public const TYPES = [
        self::TYPE_ELECTED_REPRESENTATIVE,
        self::TYPE_ADHERENT,
        self::TYPE_THEMATIC_COMMUNITY,
        self::TYPE_LRE,
        self::TYPE_THEMATIC_COMMUNITY_HEALTH,
        self::TYPE_THEMATIC_COMMUNITY_EDUCATION,
        self::TYPE_THEMATIC_COMMUNITY_ECOLOGY,
        self::TYPE_THEMATIC_COMMUNITY_EUROPE,
        self::TYPE_THEMATIC_COMMUNITY_TPE_PME,
        self::TYPE_THEMATIC_COMMUNITY_AGRICULTURE,
    ];

    public const CODES_ELECTED_REPRESENTATIVE = [
        self::CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM,
        self::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
    ];

    public const CHOICES = [
        'user_list_definition.type.elected_representative.label' => self::TYPE_ELECTED_REPRESENTATIVE,
        'user_list_definition.type.adherent.label' => self::TYPE_ADHERENT,
        'user_list_definition.type.thematic_community.label' => self::TYPE_THEMATIC_COMMUNITY,
        'user_list_definition.type.lre.label' => self::TYPE_LRE,
        'user_list_definition.type.thematic_community_health.label' => self::TYPE_THEMATIC_COMMUNITY_HEALTH,
        'user_list_definition.type.thematic_community_education.label' => self::TYPE_THEMATIC_COMMUNITY_EDUCATION,
        'user_list_definition.type.thematic_community_ecology.label' => self::TYPE_THEMATIC_COMMUNITY_ECOLOGY,
        'user_list_definition.type.thematic_community_europe.label' => self::TYPE_THEMATIC_COMMUNITY_EUROPE,
        'user_list_definition.type.thematic_community_tpe_pme.label' => self::TYPE_THEMATIC_COMMUNITY_TPE_PME,
        'user_list_definition.type.thematic_community_agriculture.label' => self::TYPE_THEMATIC_COMMUNITY_AGRICULTURE,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
