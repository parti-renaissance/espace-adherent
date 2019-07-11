<?php

namespace AppBundle\Mailchimp\Synchronisation;

use MyCLabs\Enum\Enum;

class ApplicationRequestTagLabelEnum extends Enum
{
    public const ADHERENT = 'adherent';
    public const RUNNING_MATE = 'running_mate';
    public const VOLUNTEER = 'volunteer';
}
