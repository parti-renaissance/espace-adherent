<?php

declare(strict_types=1);

namespace App\Adherent\Command;

use App\Entity\Adherent;

class SendResubscribeEmailCommand
{
    public function __construct(public readonly Adherent $adherent)
    {
    }
}
