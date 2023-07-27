<?php

namespace App\Event;

use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use MyCLabs\Enum\Enum;

class EventTypeEnum extends Enum
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_COMMITTEE = 'committee';
    public const TYPE_COALITION = 'coalition';
    public const TYPE_CAUSE = 'cause';

    public const CLASSES = [
        self::TYPE_DEFAULT => DefaultEvent::class,
        self::TYPE_COMMITTEE => CommitteeEvent::class,
    ];
}
