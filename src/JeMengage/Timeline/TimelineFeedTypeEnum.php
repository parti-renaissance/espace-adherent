<?php

namespace App\JeMengage\Timeline;

use App\Entity\Action\Action;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\Event\Event;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\News;
use App\Entity\Jecoute\Riposte;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Phoning\Campaign as PhoningCampaign;
use App\Entity\TimelineItemPrivateMessage;
use MyCLabs\Enum\Enum;

class TimelineFeedTypeEnum extends Enum
{
    public const EVENT = 'event';
    public const NEWS = 'news';
    public const RIPOSTE = 'riposte';
    public const PAP_CAMPAIGN = 'pap-campaign';
    public const PHONING_CAMPAIGN = 'phoning-campaign';
    public const SURVEY = 'survey';
    public const ACTION = 'action';
    public const PUBLICATION = 'publication';
    public const PRIVATE_MASSAGE = 'private_massage';

    public const CLASS_MAPPING = [
        News::class => self::NEWS,
        PapCampaign::class => self::PAP_CAMPAIGN,
        PhoningCampaign::class => self::PHONING_CAMPAIGN,
        Riposte::class => self::RIPOSTE,
        LocalSurvey::class => self::SURVEY,
        NationalSurvey::class => self::SURVEY,
        Event::class => self::EVENT,
        Action::class => self::ACTION,
        AdherentMessage::class => self::PUBLICATION,
        TimelineItemPrivateMessage::class => self::PRIVATE_MASSAGE,
    ];
}
