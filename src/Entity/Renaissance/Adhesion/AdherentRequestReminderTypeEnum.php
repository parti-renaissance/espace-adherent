<?php

namespace App\Entity\Renaissance\Adhesion;

enum AdherentRequestReminderTypeEnum: string
{
    case DAY_AFTER = 'day_after';
    case WEEK_AFTER = 'week_after';
    case BEFORE_REMOVAL = 'before_removal';
}
