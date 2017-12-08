<?php

namespace AppBundle;

final class Events
{
    public const COMMITTEE_CREATED = 'committee_created';
    public const COMMITTEE_UPDATED = 'committee_updated';
    public const EVENT_CREATED = 'event_created';
    public const EVENT_UPDATED = 'event_updated';
    public const EVENT_CANCELLED = 'event_cancelled';
    public const CITIZEN_ACTION_CREATED = 'citizen_action_created';
    public const CITIZEN_INITIATIVE_CREATED = 'citizen_initiative_created';
    public const CITIZEN_INITIATIVE_VALIDATED = 'citizen_initiative_validated';
    public const CITIZEN_INITIATIVE_UPDATED = 'citizen_initiativeupdated';
    public const CITIZEN_INITIATIVE_CANCELLED = 'citizen_initiativecancelled';
    public const CITIZEN_PROJECT_CREATED = 'citizen_project_created';
    public const CITIZEN_PROJECT_UPDATED = 'citizen_project_updated';
    public const CITIZEN_PROJECT_APPROVED = 'citizen_project_approved';

    private function __construct()
    {
    }
}
