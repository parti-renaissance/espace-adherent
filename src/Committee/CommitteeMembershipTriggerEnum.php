<?php

namespace App\Committee;

enum CommitteeMembershipTriggerEnum: string
{
    case MANUAL = 'manual';
    case ADDRESS_UPDATE = 'address_update';
    case COMMITTEE_EDITION = 'committee_edition';
    case ADMIN = 'admin';
}
