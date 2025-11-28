<?php

declare(strict_types=1);

namespace App\JeMengage\Hit;

enum SourceGroupEnum: string
{
    case App = 'app';
    case Email = 'email';
    case Notification = 'notification';
}
