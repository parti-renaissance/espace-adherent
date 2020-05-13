<?php

namespace App\AdherentMessage;

use MyCLabs\Enum\Enum;

class AdherentMessageStatusEnum extends Enum
{
    public const DRAFT = 'draft';
    public const SENT_SUCCESSFULLY = 'sent';
}
