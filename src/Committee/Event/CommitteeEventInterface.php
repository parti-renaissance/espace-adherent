<?php

namespace AppBundle\Committee\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

interface CommitteeEventInterface
{
    public function getCommittee(): ?Committee;

    public function getAdherent(): Adherent;
}
