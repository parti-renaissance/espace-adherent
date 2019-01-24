<?php

namespace AppBundle\Entity\Report;

use MyCLabs\Enum\Enum;

class ReportReasonEnum extends Enum
{
    public const REASON_EN_MARCHE_VALUES = 'en_marche_values';
    public const REASON_INAPPROPRIATE = 'inappropriate';
    public const REASON_COMMERCIAL_CONTENT = 'commercial_content';
    public const REASON_OTHER = 'other';
    public const REASON_ILLICIT_CONTENT = 'illicit_content';
    public const REASON_INTELLECTUAL_PROPERTY = 'intellectual_property';

    public const REASONS_LIST = [
        self::REASON_INTELLECTUAL_PROPERTY,
        self::REASON_ILLICIT_CONTENT,
        self::REASON_COMMERCIAL_CONTENT,
        self::REASON_OTHER,
    ];
}
