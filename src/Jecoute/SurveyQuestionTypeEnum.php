<?php

declare(strict_types=1);

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

class SurveyQuestionTypeEnum extends Enum
{
    public const MULTIPLE_CHOICE_TYPE = 'multiple_choice';
    public const UNIQUE_CHOICE_TYPE = 'unique_choice';
    public const SIMPLE_FIELD = 'simple_field';

    public const MULTIPLE_CHOICE_TYPE_LABEL = 'Choix multiples';
    public const UNIQUE_CHOICE_TYPE_LABEL = 'Choix unique';
    public const SIMPLE_FIELD_LABEL = 'Champ libre';

    public const CHOICE_TYPES = [
        self::UNIQUE_CHOICE_TYPE,
        self::MULTIPLE_CHOICE_TYPE,
    ];

    public static function all(): array
    {
        return [
            self::MULTIPLE_CHOICE_TYPE_LABEL => self::MULTIPLE_CHOICE_TYPE,
            self::UNIQUE_CHOICE_TYPE_LABEL => self::UNIQUE_CHOICE_TYPE,
            self::SIMPLE_FIELD_LABEL => self::SIMPLE_FIELD,
        ];
    }
}
