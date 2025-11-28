<?php

declare(strict_types=1);

namespace App\JeMengage\Hit;

enum EventTypeEnum: string
{
    case ActivitySession = 'activity_session';
    case Impression = 'impression';
    case Open = 'open';
    case Click = 'click';
}
