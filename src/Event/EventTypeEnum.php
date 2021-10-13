<?php

namespace App\Event;

use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\InstitutionalEvent;
use App\Entity\Event\MunicipalEvent;
use MyCLabs\Enum\Enum;

class EventTypeEnum extends Enum
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_COMMITTEE = 'committee';
    public const TYPE_INSTITUTIONAL = 'institutional';
    public const TYPE_MUNICIPAL = 'municipal';
    public const TYPE_COALITION = 'coalition';
    public const TYPE_CAUSE = 'cause';

    public const CLASSES = [
        self::TYPE_DEFAULT => DefaultEvent::class,
        self::TYPE_COMMITTEE => CommitteeEvent::class,
        self::TYPE_INSTITUTIONAL => InstitutionalEvent::class,
        self::TYPE_MUNICIPAL => MunicipalEvent::class,
        self::TYPE_COALITION => CoalitionEvent::class,
        self::TYPE_CAUSE => CauseEvent::class,
    ];
}
