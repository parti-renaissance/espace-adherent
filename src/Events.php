<?php

namespace AppBundle;

final class Events
{
    const COMMITTEE_CREATED = 'committee_created';
    const COMMITTEE_UPDATED = 'committee_updated';
    const EVENT_CREATED = 'event_created';
    const EVENT_UPDATED = 'event_updated';
    const EVENT_CANCELLED = 'event_cancelled';
    const CITIZEN_INITIATIVE_CREATED = 'citizen_initiative_created';
    const CITIZEN_INITIATIVE_VALIDATED = 'citizen_initiative_validated';
    const CITIZEN_INITIATIVE_UPDATED = 'citizen_initiativeupdated';
    const CITIZEN_INITIATIVE_CANCELLED = 'citizen_initiativecancelled';
    const MOOC_EVENT_CREATED = 'mooc_event_created';
    const MOOC_EVENT_VALIDATED = 'mooc_event_validated';
    const MOOC_EVENT_UPDATED = 'mooc_event_updated';
    const MOOC_EVENT_CANCELLED = 'mooc_event_cancelled';

    private function __construct()
    {
    }
}
