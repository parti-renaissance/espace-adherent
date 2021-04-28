<?php

namespace App\Event;

use MyCLabs\Enum\Enum;

class EventTypeEnum extends Enum
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_COMMITTEE = 'committee';
    public const TYPE_CITIZEN_ACTION = 'citizen_action';
    public const TYPE_INSTITUTIONAL = 'institutional';
    public const TYPE_MUNICIPAL = 'municipal';
    public const TYPE_COALITION = 'coalition';
    public const TYPE_CAUSE = 'cause';
}
