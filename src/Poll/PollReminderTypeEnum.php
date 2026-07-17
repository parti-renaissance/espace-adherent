<?php

declare(strict_types=1);

namespace App\Poll;

enum PollReminderTypeEnum: string
{
    case LAUNCH = 'launch';
    case REMINDER_J1 = 'reminder_j1';
    case CLOSING_H1 = 'closing_h1';
}
