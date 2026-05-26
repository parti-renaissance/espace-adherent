<?php

declare(strict_types=1);

namespace App\Membership\Signup\Command;

use App\Entity\Adherent;

class SendSignupConfirmationCommand
{
    public function __construct(public readonly Adherent $adherent)
    {
    }
}
