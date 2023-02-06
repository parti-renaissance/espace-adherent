<?php

namespace App\MyTeam;

use MyCLabs\Enum\Enum;

class RoleEnum extends Enum
{
    public const GENERAL_SECRETARY = 'general_secretary';
    public const TREASURER = 'treasurer';
    public const MOBILIZATION_MANAGER = 'mobilization_manager';
    public const LOGISTICS_MANAGER = 'logistics_manager';
    public const COMMUNICATION_MANAGER = 'communication_manager';
    public const COMPLIANCE_AND_FINANCE_MANAGER = 'compliance_and_finance_manager';

    public const LABELS = [
        self::GENERAL_SECRETARY => 'Secrétaire général',
        self::TREASURER => 'Trésorier',
        self::MOBILIZATION_MANAGER => 'Responsable mobilisation',
        self::LOGISTICS_MANAGER => 'Responsable logistique',
        self::COMMUNICATION_MANAGER => 'Responsable communication',
        self::COMPLIANCE_AND_FINANCE_MANAGER => 'Responsables conformité et finance',
    ];

    public const ALL = [
        self::GENERAL_SECRETARY,
        self::TREASURER,
        self::MOBILIZATION_MANAGER,
        self::LOGISTICS_MANAGER,
        self::COMMUNICATION_MANAGER,
        self::COMPLIANCE_AND_FINANCE_MANAGER,
    ];
}
