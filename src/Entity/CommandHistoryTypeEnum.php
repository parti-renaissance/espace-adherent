<?php

declare(strict_types=1);

namespace App\Entity;

enum CommandHistoryTypeEnum: string
{
    case NEW_MEMBERSHIP_NOTIFICATION = 'new_membership_notification';
}
