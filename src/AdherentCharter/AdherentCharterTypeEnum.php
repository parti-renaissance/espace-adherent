<?php

declare(strict_types=1);

namespace App\AdherentCharter;

use MyCLabs\Enum\Enum;

class AdherentCharterTypeEnum extends Enum
{
    public const TYPE_COMMITTEE_HOST = 'committee_host';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const TYPE_CANDIDATE = 'candidate';
    public const TYPE_PHONING_CAMPAIGN = 'phoning_campaign';
    public const TYPE_PAP_CAMPAIGN = 'pap_campaign';
}
