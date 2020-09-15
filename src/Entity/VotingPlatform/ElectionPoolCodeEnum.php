<?php

namespace App\Entity\VotingPlatform;

use App\ValueObject\Genders;
use MyCLabs\Enum\Enum;

class ElectionPoolCodeEnum extends Enum
{
    public const FEMALE = Genders::FEMALE;
    public const MALE = Genders::MALE;
}
