<?php

namespace App\RepublicanSilence;

use App\Exception\InvalidAdherentTypeException;
use App\RepublicanSilence\TagExtractor\CitizenProjectReferentTagExtractor;
use App\RepublicanSilence\TagExtractor\CommitteeReferentTagExtractor;
use App\RepublicanSilence\TagExtractor\DistrictReferentTagExtractor;
use App\RepublicanSilence\TagExtractor\MunicipalChefReferentTagExtractor;
use App\RepublicanSilence\TagExtractor\ReferentTagExtractor;
use App\RepublicanSilence\TagExtractor\ReferentTagExtractorInterface;
use App\RepublicanSilence\TagExtractor\SenatorReferentTagExtractor;

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

            case ReferentTagExtractorInterface::ADHERENT_TYPE_MUNICIPAL_CHIEF:
                return new MunicipalChefReferentTagExtractor();

            case ReferentTagExtractorInterface::ADHERENT_TYPE_SENATOR:
                return new SenatorReferentTagExtractor();
        }

        throw new InvalidAdherentTypeException(sprintf('Adherent type [%d] is invalid', $type));
    }
}
