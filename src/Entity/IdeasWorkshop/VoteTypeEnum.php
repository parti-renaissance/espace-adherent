<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

class VoteTypeEnum extends Enum
{
    public const IMPORTANT = 'important';
    public const FEASIBLE = 'feasible';
    public const INNOVATIVE = 'innovative';
}
