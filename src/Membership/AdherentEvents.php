<?php

namespace App\Membership;

final class AdherentEvents
{
    public const REGISTRATION_COMPLETED = 'adherent.account.registration_completed';
    public const PROFILE_UPDATED = 'adherent.profile_updated';

    private function __construct()
    {
    }
}
