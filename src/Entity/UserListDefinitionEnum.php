<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class UserListDefinitionEnum extends Enum
{
    public const TYPE_ELECTED_REPRESENTATIVE = 'elected_representative';
    public const TYPE_ADHERENT = 'adherent';

    public const CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM = 'supporting_la_rem';
    public const CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER = 'instances_member';

    public const TYPES = [
        self::TYPE_ELECTED_REPRESENTATIVE,
        self::TYPE_ADHERENT,
    ];

    public const CODES_ELECTED_REPRESENTATIVE = [
        self::CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM,
        self::CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
    ];

    public const CHOICES = [
        'user_list_definition.type.elected_representative.label' => self::TYPE_ELECTED_REPRESENTATIVE,
        'user_list_definition.type.adherent.label' => self::TYPE_ADHERENT,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
