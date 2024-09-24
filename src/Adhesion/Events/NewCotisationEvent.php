<?php

namespace App\Adhesion\Events;

use App\Entity\Adherent;
use App\Entity\Donation;
use App\Membership\Event\AdherentEvent;

class NewCotisationEvent extends AdherentEvent
{
    public function __construct(Adherent $adherent, public readonly Donation $donation)
    {
        parent::__construct($adherent);
    }
}
