<?php

namespace App\Entity;

enum CommandHistoryTypeEnum: string
{
    case NEW_MEMBERSHIP_NOTIFICATION = 'new_membership_notification';
}
