<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class UserListDefinitionEnum extends Enum
{
    public const TYPE_ELECTED_REPRESENTATIVE = 'elected_representative';

    public const CODE_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM = 'supporting_la_rem';
    public const CODE_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER = 'instances_member';

    public const TYPES = [
        self::TYPE_ELECTED_REPRESENTATIVE,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
