<?php

declare(strict_types=1);

namespace App\Poll;

enum PollReminderTypeEnum: string
{
    case LAUNCH = 'launch';
    case REMINDER_H8 = 'reminder_h8';
    case CLOSING_H1 = 'closing_h1';
}
