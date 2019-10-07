<?php

namespace AppBundle\Entity\AdherentCharter;

use AppBundle\Entity\Adherent;

interface AdherentCharterInterface
{
    public function setAdherent(Adherent $adherent): void;
}
