<?php

declare(strict_types=1);

namespace App\Procuration;

enum MatchingHistoryActionEnum: string
{
    case MATCH = 'match';
    case UNMATCH = 'unmatch';
}
