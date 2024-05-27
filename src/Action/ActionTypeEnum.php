<?php

namespace App\Action;

use MyCLabs\Enum\Enum;

class ActionTypeEnum extends Enum
{
    public const PAP = 'pap';
    public const BOITAGE = 'boitage';
    public const TRACTAGE = 'tractage';
    public const COLLAGE = 'collage';
}
