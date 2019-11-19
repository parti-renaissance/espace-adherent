<?php

namespace AppBundle\Entity\Mooc;

use MyCLabs\Enum\Enum;

class MoocElementTypeEnum extends Enum
{
    public const IMAGE = 'image';
    public const VIDEO = 'video';
    public const QUIZ = 'quiz';
}
