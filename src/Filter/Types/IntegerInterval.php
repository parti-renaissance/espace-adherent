<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class IntegerInterval extends AbstractFilter
{
    protected function _getType(): string
    {
        return FilterTypeEnum::INTEGER_INTERVAL;
    }
}
