<?php

declare(strict_types=1);

namespace App;

final class Events
{
    public const EVENT_CREATED = 'event.created';
    public const EVENT_PRE_UPDATE = 'event.pre_update';
    public const EVENT_UPDATED = 'event.updated';
    public const EVENT_CANCELLED = 'event.cancelled';
    public const EVENT_REGISTRATION_CREATED = 'event_registration_created';

    private function __construct()
    {
    }
}
