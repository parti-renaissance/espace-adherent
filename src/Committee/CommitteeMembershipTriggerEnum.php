<?php

namespace App\Committee;

enum CommitteeMembershipTriggerEnum: string
{
    case Manual = 'manual';
    case AddressUpdate = 'address_update';
    case CommitteeCreation = 'committee_creation';
}
