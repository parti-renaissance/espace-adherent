<?php

declare(strict_types=1);

namespace App\Entity\Filesystem;

use MyCLabs\Enum\Enum;

class FilePermissionEnum extends Enum
{
    public const ALL = 'all';
    public const CANDIDATE_REGIONAL_HEADED = 'candidate_regional_headed';
    public const CANDIDATE_REGIONAL_LEADER = 'candidate_regional_leader';
    public const CANDIDATE_DEPARTMENTAL = 'candidate_departmental';
}
