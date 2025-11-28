<?php

declare(strict_types=1);

namespace App\Adhesion;

enum AdherentRequestReminderTypeEnum: string
{
    case AFTER_ONE_HOUR = 'after_one_hour';
    case NEXT_SATURDAY = 'next_saturday';
    case AFTER_THREE_WEEKS = 'after_three_weeks';
}
