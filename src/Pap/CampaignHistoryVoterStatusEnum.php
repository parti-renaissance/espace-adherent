<?php

declare(strict_types=1);

namespace App\Pap;

use MyCLabs\Enum\Enum;

class CampaignHistoryVoterStatusEnum extends Enum
{
    public const NOT_VOTER = 'not_voter';
    public const NOT_REGISTERED = 'not_registered';
    public const REGISTERED = 'registered';
    public const REGISTERED_ELSEWHERE = 'registered_elsewhere';

    public const LABELS = [
        self::NOT_VOTER => 'Pas électeur',
        self::NOT_REGISTERED => 'Pas inscrit',
        self::REGISTERED => 'Inscrit sur les listes',
        self::REGISTERED_ELSEWHERE => 'Inscrit ailleurs',
    ];
}
