<?php

declare(strict_types=1);

namespace App\Phoning;

use MyCLabs\Enum\Enum;

class CampaignHistoryEngagementEnum extends Enum
{
    public const ACTIVE = 'active';
    public const WANT_TO_ENGAGE = 'want_to_engage';
    public const DONT_WANT_TO_ENGAGE = 'dont_want_to_engage';

    public const LABELS = [
        self::ACTIVE => 'Déjà actif',
        self::WANT_TO_ENGAGE => 'Souhaite se mobiliser',
        self::DONT_WANT_TO_ENGAGE => 'Ne le souhaite pas',
    ];
}
