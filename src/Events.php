<?php

namespace AppBundle;

final class Events
{
    const COMMITTEE_CREATED = 'committee_created';
    const COMMITTEE_UPDATED = 'committee_updated';
    const EVENT_CREATED = 'event_created';
    const EVENT_UPDATED = 'event_updated';
    const EVENT_CANCELLED = 'event_cancelled';

    private function __construct()
    {
    }
}
