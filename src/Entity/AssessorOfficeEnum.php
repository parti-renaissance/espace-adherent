<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

class AssessorOfficeEnum extends Enum
{
    public const HOLDER = 'holder';
    public const SUBSTITUTE = 'substitute';

    public const ALL = [
        self::HOLDER,
        self::SUBSTITUTE,
    ];

    public const CHOICES = [
        'assessor_request.office.holder.label' => self::HOLDER,
        'assessor_request.office.substitute.label' => self::SUBSTITUTE,
    ];
}
