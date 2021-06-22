<?php

namespace App\Instance;

use MyCLabs\Enum\Enum;

final class InstanceQualityEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const EUROPEAN_DEPUTY = 'european_deputy';
    public const SENATOR = 'senator';
    public const REGIONAL_COUNCIL_PRESIDENT = 'regional_council_president';
    public const DEPARTMENT_COUNCIL_PRESIDENT = 'department_council_president';
    public const TERRITORIAL_COUNCIL_PRESIDENT = 'territorial_council_president';
    public const GOVERNMENT_MEMBER = 'government_member';
    public const QUATUOR_MEMBER = 'quatuor_member';
}
