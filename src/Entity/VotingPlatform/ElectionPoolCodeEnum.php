<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\ValueObject\Genders;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use MyCLabs\Enum\Enum;

class ElectionPoolCodeEnum extends Enum
{
    public const FEMALE = Genders::FEMALE;
    public const MALE = Genders::MALE;

    public const COMMITTEE_SUPERVISOR = DesignationTypeEnum::COMMITTEE_SUPERVISOR;

    public const COMMITTEE_ADHERENT = [
        self::FEMALE,
        self::MALE,
    ];

    public const COPOL = [
        TerritorialCouncilQualityEnum::LEADER,
        TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR,
        TerritorialCouncilQualityEnum::CORSICA_ASSEMBLY_MEMBER,
        TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
        TerritorialCouncilQualityEnum::CITY_COUNCILOR,
        TerritorialCouncilQualityEnum::CONSULAR_COUNCILOR,
        TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR,
        TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT,
    ];
}
