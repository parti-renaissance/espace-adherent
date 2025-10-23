<?php

namespace App\JeMengage\Hit;

enum SourceGroupEnum: string
{
    case App = 'app';
    case Email = 'email';
    case Notification = 'notification';
}
