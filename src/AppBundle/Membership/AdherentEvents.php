<?php

namespace AppBundle\Membership;

final class AdherentEvents
{
    const ACTIVATION_COMPLETED = 'adherent.account.activation_completed';
    const REGISTRATION_COMPLETED = 'adherent.account.registration_completed';

    private function __construct()
    {
    }
}
