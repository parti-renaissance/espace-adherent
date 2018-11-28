<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class IdeaStatusEnum extends Enum
{
    public const IN_PROGRESS = 'IN PROGRESS';
    public const PUBLISHED = 'PUBLISHED';
    public const REFUSED = 'REFUSED';
}
