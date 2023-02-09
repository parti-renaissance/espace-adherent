<?php

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterTypeEnum;

class Text extends AbstractFilter
{
    protected function _getType(): string
    {
        return FilterTypeEnum::TEXT;
    }
}
