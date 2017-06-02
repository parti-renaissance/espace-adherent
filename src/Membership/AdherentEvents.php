<?php

namespace AppBundle\Membership;

final class AdherentEvents
{
    const REGISTRATION_COMPLETED = 'adherent.account.registration_completed';
    const PROFILE_UPDATED = 'adherent.profile_updated';

    private function __construct()
    {
    }
}
