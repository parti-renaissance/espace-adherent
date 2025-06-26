<?php

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Committee;
use Symfony\Contracts\EventDispatcher\Event;

class UserCommitteeActionEvent extends Event
{
    public function __construct(
        public readonly Adherent $adherent,
        public readonly Committee $committee,
    ) {
    }
}
