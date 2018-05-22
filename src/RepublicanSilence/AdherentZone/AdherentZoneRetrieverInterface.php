<?php

namespace AppBundle\RepublicanSilence\AdherentZone;

use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;

interface AdherentZoneRetrieverInterface
{
    public const ADHERENT_TYPE_REFERENT = 0;
    public const ADHERENT_TYPE_ANIMATOR = 1;
    public const ADHERENT_TYPE_HOST = 2;

    public function getAdherentZone(Adherent $adherent, Request $request): array;
}
