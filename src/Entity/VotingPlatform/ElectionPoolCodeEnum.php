<?php

namespace App\Entity\VotingPlatform;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\ValueObject\Genders;
use MyCLabs\Enum\Enum;

class ElectionPoolCodeEnum extends Enum
{
    public const FEMALE = Genders::FEMALE;
    public const MALE = Genders::MALE;

    public const COMMITTEE_ADHERENT = [
        self::FEMALE,
        self::MALE,
    ];

    public const COPOL = [
        TerritorialCouncilQualityEnum::LEADER,
        TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR,
        TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
        TerritorialCouncilQualityEnum::CITY_COUNCILOR,
        TerritorialCouncilQualityEnum::CONSULAR_CONSELOR,
        TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
        TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
    ];
}
