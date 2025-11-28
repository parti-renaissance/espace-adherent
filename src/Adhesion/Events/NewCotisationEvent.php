<?php

declare(strict_types=1);

namespace App\Adhesion\Events;

use App\Entity\Adherent;
use App\Entity\Donation;
use App\Membership\Event\UserEvent;

class NewCotisationEvent extends UserEvent
{
    public function __construct(Adherent $adherent, public readonly Donation $donation)
    {
        parent::__construct($adherent);
    }
}
