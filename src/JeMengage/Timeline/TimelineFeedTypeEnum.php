<?php

namespace App\JeMengage\Timeline;

use App\Entity\Jecoute\News;
use App\Entity\Jecoute\Riposte;
use App\Entity\Jecoute\Survey;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Phoning\Campaign as PhoningCampaign;
use MyCLabs\Enum\Enum;

class TimelineFeedTypeEnum extends Enum
{
    public const EVENT = 'event';
    public const NEWS = 'news';
    public const RIPOSTE = 'riposte';
    public const PAP_CAMPAIGN = 'pap-campaign';
    public const PHONING_CAMPAIGN = 'phoning-campaign';
    public const SURVEY = 'survey';

    public const CLASS_MAPPING = [
        News::class => self::NEWS,
        PapCampaign::class => self::PAP_CAMPAIGN,
        PhoningCampaign::class => self::PHONING_CAMPAIGN,
        Riposte::class => self::RIPOSTE,
        Survey::class => self::SURVEY,
    ];
}
