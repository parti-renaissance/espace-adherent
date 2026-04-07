<?php

declare(strict_types=1);

namespace App\Firebase;

enum PushNotificationStatusEnum: string
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case PARTIAL = 'partial';
}
