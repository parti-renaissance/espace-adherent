<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

enum SesEngagementType
{
    case OPEN;
    case CLICK;
}
