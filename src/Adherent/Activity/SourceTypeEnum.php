<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

enum SourceTypeEnum: string
{
    case ActionHistory = 'action_history';
    case Hit = 'hit';
}
