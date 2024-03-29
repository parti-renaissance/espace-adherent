<?php

namespace App\Procuration\V2;

enum MatchingHistoryActionEnum: string
{
    case MATCH = 'match';
    case UNMATCH = 'unmatch';
}
