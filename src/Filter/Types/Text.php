<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class Text extends AbstractFilter
{
    protected function _getType(): string
    {
        return FilterTypeEnum::TEXT;
    }
}
