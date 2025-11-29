<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use MyCLabs\Enum\Enum;

class AdherentMessageStatusEnum extends Enum
{
    public const DRAFT = 'draft';
    public const SENT = 'sent';
}
