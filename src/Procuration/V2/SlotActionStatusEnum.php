<?php

namespace App\Procuration\V2;

enum SlotActionStatusEnum: string
{
    case MATCH = 'match';
    case UNMATCH = 'unmatch';
    case STATUS_UPDATE = 'status_update';
}
