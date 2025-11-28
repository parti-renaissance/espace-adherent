<?php

declare(strict_types=1);

namespace App\Jecoute;

use MyCLabs\Enum\Enum;

final class SurveyTypeEnum extends Enum
{
    public const LOCAL = 'local';
    public const NATIONAL = 'national';
}
