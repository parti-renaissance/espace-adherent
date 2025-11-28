<?php

declare(strict_types=1);

namespace App\Admin\Committee;

use MyCLabs\Enum\Enum;

class CommitteeAdherentMandateTypeEnum extends Enum
{
    public const TYPE_SUPERVISOR = 'supervisor';
    public const TYPE_DESIGNED_ADHERENT = 'designed_adherent';

    public const SUPERVISOR_FEMALE = 'supervisor_female';
    public const SUPERVISOR_MALE = 'supervisor_male';
    public const PROVISIONAL_SUPERVISOR_FEMALE = 'provisional_supervisor_female';
    public const PROVISIONAL_SUPERVISOR_MALE = 'provisional_supervisor_male';
    public const ELECTED_ADHERENT_FEMALE = 'elected_adherent_female';
    public const ELECTED_ADHERENT_MALE = 'elected_adherent_male';

    public static function getTypesForCreation(): array
    {
        return [
            self::PROVISIONAL_SUPERVISOR_FEMALE,
            self::PROVISIONAL_SUPERVISOR_MALE,
            self::ELECTED_ADHERENT_FEMALE,
            self::ELECTED_ADHERENT_MALE,
        ];
    }
}
