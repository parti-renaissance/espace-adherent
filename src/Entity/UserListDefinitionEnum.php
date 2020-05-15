<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

final class UserListDefinitionEnum extends Enum
{
    public const TYPE_ELECTED_REPRESENTATIVE = 'elected_representative';

    public const LABEL_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM = 'Sympathisant(e) LaREM';
    public const LABEL_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER = 'Participe aux instances';

    public const TYPES = [
        self::TYPE_ELECTED_REPRESENTATIVE,
    ];

    public const TYPE_ELECTED_REPRESENTATIVE_LABELS = [
        self::LABEL_ELECTED_REPRESENTATIVE_SUPPORTING_LA_REM,
        self::LABEL_ELECTED_REPRESENTATIVE_INSTANCES_MEMBER,
    ];

    public static function getTypes(): array
    {
        return self::TYPES;
    }
}
