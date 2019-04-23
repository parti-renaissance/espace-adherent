<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Exception\InvalidAdherentTypeException;
use AppBundle\RepublicanSilence\TagExtractor\CitizenProjectReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\CommitteeReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\DistrictReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\ReferentTagExtractor;
use AppBundle\RepublicanSilence\TagExtractor\ReferentTagExtractorInterface;

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

            case ReferentTagExtractorInterface::ADHERENT_TYPE_DEPUTY:
                return new DistrictReferentTagExtractor();
        }

        throw new InvalidAdherentTypeException(sprintf('Adherent type [%d] is invalid', $type));
    }
}
