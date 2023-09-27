<?php

namespace App\Adherent\Notification;

enum NotificationTypeEnum: string
{
    case NEW_SYMPATHISER = 'new_sympathiser';
    case NEW_MEMBERSHIP = 'new_membership';
}
