<?php

declare(strict_types=1);

namespace App\Procuration\V2;

enum MatchingHistoryActionEnum: string
{
    case MATCH = 'match';
    case UNMATCH = 'unmatch';
}
