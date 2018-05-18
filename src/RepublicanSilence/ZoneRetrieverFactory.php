<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Exception\InvalidAdherentTypeException;
use AppBundle\RepublicanSilence\AdherentZone\AdherentZoneRetrieverInterface;
use AppBundle\RepublicanSilence\AdherentZone\CitizenProjectAdherentZoneRetriever;
use AppBundle\RepublicanSilence\AdherentZone\CommitteeAdherentZoneRetriever;
use AppBundle\RepublicanSilence\AdherentZone\ReferentZoneRetriever;

abstract class ZoneRetrieverFactory
{
    public static function create(int $type): AdherentZoneRetrieverInterface
    {
        switch ($type) {
            case AdherentZoneRetrieverInterface::ADHERENT_TYPE_REFERENT:
                return new ReferentZoneRetriever();

            case AdherentZoneRetrieverInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR:
                return new CommitteeAdherentZoneRetriever();

            case AdherentZoneRetrieverInterface::ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR:
                return new CitizenProjectAdherentZoneRetriever();
        }

        throw new InvalidAdherentTypeException(sprintf('Adherent type [%d] is invalid', $type));
    }
}
