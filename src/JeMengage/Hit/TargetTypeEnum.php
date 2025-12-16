<?php

declare(strict_types=1);

namespace App\JeMengage\Hit;

enum TargetTypeEnum: string
{
    case Event = 'event';
    case Publication = 'publication';
    case Action = 'action';
    case News = 'news';
    case Alert = 'alert';
    case TransactionalMessage = 'transactional_message';
}
