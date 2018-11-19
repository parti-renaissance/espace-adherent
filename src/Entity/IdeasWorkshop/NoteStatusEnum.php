<?php

namespace AppBundle\Entity\IdeasWorkshop;

use MyCLabs\Enum\Enum;

final class NoteStatusEnum extends Enum
{
    public const IN_PROGRESS = 'IN PROGRESS';
    public const PUBLISHED = 'PUBLISHED';
    public const REFUSED = 'REFUSED';
}
