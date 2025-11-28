<?php

declare(strict_types=1);

namespace App\VotingPlatform\Designation;

use MyCLabs\Enum\Enum;

final class DesignationGlobalZoneEnum extends Enum
{
    public const FRANCE = 'FR';
    public const FDE = 'FDE';
    public const OUTRE_MER = 'OUTRE_MER';
}
