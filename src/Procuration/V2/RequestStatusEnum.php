<?php

namespace App\Procuration\V2;

enum RequestStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case MANUAL = 'manual';
}
