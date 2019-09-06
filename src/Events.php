<?php

namespace AppBundle;

final class Events
{
    public const COMMITTEE_CREATED = 'committee.created';
    public const COMMITTEE_UPDATED = 'committee.updated';
    public const COMMITTEE_DELETED = 'committee.deleted';
    public const EVENT_CREATED = 'event.created';
    public const EVENT_UPDATED = 'event.updated';
    public const EVENT_DELETED = 'event.deleted';
    public const EVENT_CANCELLED = 'event.cancelled';
    public const INSTITUTIONAL_EVENT_CREATED = 'institutional_event.created';
    public const INSTITUTIONAL_EVENT_UPDATED = 'institutional_event.updated';
    public const INSTITUTIONAL_EVENT_DELETED = 'institutional_event.deleted';
    public const INSTITUTIONAL_EVENT_CANCELLED = 'institutional_event.cancelled';
    public const EVENT_REGISTRATION_CREATED = 'event_registration_created';
    public const CITIZEN_ACTION_CREATED = 'citizen_action.created';
    public const CITIZEN_ACTION_UPDATED = 'citizen_action.updated';
    public const CITIZEN_ACTION_DELETED = 'citizen_action.deleted';
    public const CITIZEN_ACTION_CANCELLED = 'citizen_action.cancelled';
    public const CITIZEN_PROJECT_CREATED = 'citizen_project.created';
    public const CITIZEN_PROJECT_UPDATED = 'citizen_project.updated';
    public const CITIZEN_PROJECT_DELETED = 'citizen_project.deleted';
    public const CITIZEN_PROJECT_APPROVED = 'citizen_project.approved';
    public const CITIZEN_PROJECT_FOLLOWER_ADDED = 'citizen_project_follower_added';
    public const CITIZEN_PROJECT_FOLLOWER_REMOVED = 'citizen_project_follower_removed';
    public const CHEZVOUS_MEASURE_TYPE_UPDATED = 'chezvous_measure_type.updated';
    public const CHEZVOUS_MEASURE_TYPE_DELETED = 'chezvous_measure_type.deleted';

    private function __construct()
    {
    }
}
