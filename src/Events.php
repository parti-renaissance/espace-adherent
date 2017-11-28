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
    const CITIZEN_PROJECT_CREATED = 'citizen_project_created';
    const CITIZEN_PROJECT_UPDATED = 'citizen_project_updated';
    const CITIZEN_PROJECT_APPROVE = 'citizen_project_approve';

    private function __construct()
    {
    }
}
