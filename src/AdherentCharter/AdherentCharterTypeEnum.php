<?php

namespace App\AdherentCharter;

use MyCLabs\Enum\Enum;

class AdherentCharterTypeEnum extends Enum
{
    public const TYPE_COMMITTEE_HOST = 'committee_host';
    public const TYPE_REFERENT = 'referent';
    public const TYPE_DEPUTY = 'deputy';
    public const TYPE_SENATOR = 'senator';
    public const TYPE_SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const TYPE_LRE = 'la_republique_ensemble';
    public const TYPE_LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const TYPE_CANDIDATE = 'candidate';
    public const TYPE_THEMATIC_COMMUNITY_CHIEF = 'thematic_community_chief';
    public const TYPE_PHONING_CAMPAIGN = 'phoning_campaign';
    public const TYPE_PAP_CAMPAIGN = 'pap_campaign';
}
