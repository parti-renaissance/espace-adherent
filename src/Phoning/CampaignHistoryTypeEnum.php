<?php

declare(strict_types=1);

namespace App\Phoning;

use MyCLabs\Enum\Enum;

class CampaignHistoryTypeEnum extends Enum
{
    public const IN_APP = 'in-app';
    public const OUT_OF_APP = 'out-of-app';

    public const LABELS = [
        self::IN_APP => 'Dans l\'app',
        self::OUT_OF_APP => 'Hors app',
    ];
}
