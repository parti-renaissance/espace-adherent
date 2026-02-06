<?php

declare(strict_types=1);

namespace App\Procuration;

enum SlotActionStatusEnum: string
{
    case MATCH = 'match';
    case UNMATCH = 'unmatch';
    case STATUS_UPDATE = 'status_update';
}
