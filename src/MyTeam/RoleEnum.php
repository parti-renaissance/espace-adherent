<?php

namespace App\MyTeam;

use MyCLabs\Enum\Enum;

class RoleEnum extends Enum
{
    public const MOBILIZATION_MANAGER = 'mobilization_manager';
    public const LOGISTICS_MANAGER = 'logistics_manager';
    public const COMMUNICATION_MANAGER = 'communication_manager';
    public const COMPLIANCE_AND_FINANCE_MANAGERS = 'compliance_and_finance_managers';

    public const LABELS = [
        self::MOBILIZATION_MANAGER => 'Responsable mobilisation',
        self::LOGISTICS_MANAGER => 'Responsable logistique',
        self::COMMUNICATION_MANAGER => 'Responsable communication',
        self::COMPLIANCE_AND_FINANCE_MANAGERS => 'Responsables conformité et finance',
    ];

    public const ALL = [
        self::MOBILIZATION_MANAGER,
        self::LOGISTICS_MANAGER,
        self::COMMUNICATION_MANAGER,
        self::COMPLIANCE_AND_FINANCE_MANAGERS,
    ];
}
