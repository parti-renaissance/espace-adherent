<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class ThreadStatusEnum extends Enum
{
    public const APPROVED = 'APPROVED';
    public const DELETED = 'DELETED';
    public const SUBMITTED = 'SUBMITTED';
}
