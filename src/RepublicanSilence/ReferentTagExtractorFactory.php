<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Exception\InvalidAdherentTypeException;
use AppBundle\RepublicanSilence\TagExtractor\ReferentTagExtractorInterface;
use AppBundle\RepublicanSilence\TagExtractor\CitizenProjectReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\CommitteeReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\ReferentTagExtractor;

abstract class ReferentTagExtractorFactory
{
    public static function create(int $type): ReferentTagExtractorInterface
    {
        switch ($type) {
            case ReferentTagExtractorInterface::ADHERENT_TYPE_REFERENT:
                return new ReferentTagExtractor();

            case ReferentTagExtractorInterface::ADHERENT_TYPE_COMMITTEE_ADMINISTRATOR:
                return new CommitteeReferentTagExtractor();

            case ReferentTagExtractorInterface::ADHERENT_TYPE_CITIZEN_PROJECT_ADMINISTRATOR:
                return new CitizenProjectReferentTagExtractor();
        }

        throw new InvalidAdherentTypeException(sprintf('Adherent type [%d] is invalid', $type));
    }
}
