<?php

namespace AppBundle\RepublicanSilence\AdherentZone;

use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;

class ReferentZoneRetriever implements AdherentZoneRetrieverInterface
{
    public function getAdherentZone(Adherent $adherent, Request $request): array
    {
        return $adherent->getManagedArea()->getCodes();
    }
}
