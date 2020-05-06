<?php

namespace App\Committee\Event;

use App\Entity\Adherent;
use App\Entity\Committee;

interface CommitteeEventInterface
{
    public function getCommittee(): ?Committee;

    public function getAdherent(): Adherent;
}
