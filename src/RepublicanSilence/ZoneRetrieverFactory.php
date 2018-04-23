<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\RepublicanSilence\AdherentZone\AdherentZoneRetrieverInterface;
use AppBundle\RepublicanSilence\AdherentZone\CommitteeHostZoneRetriever;
use AppBundle\RepublicanSilence\AdherentZone\ReferentZoneRetriever;

abstract class ZoneRetrieverFactory
{
    public static function create(int $type): AdherentZoneRetrieverInterface
    {
        switch ($type) {
            case AdherentZoneRetrieverInterface::ADHERENT_TYPE_REFERENT:
                return new ReferentZoneRetriever();

            case AdherentZoneRetrieverInterface::ADHERENT_TYPE_HOST:
                return new CommitteeHostZoneRetriever();
        }
    }
}
