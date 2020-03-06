<?php

namespace AppBundle\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class LaREMSupportEnum extends Enum
{
    public const INVESTED = 'invested';
    public const OFFICIAL = 'official';
    public const INFORMAL = 'informal';
    public const NOT_SUPPORTED = 'not_supported';
}
