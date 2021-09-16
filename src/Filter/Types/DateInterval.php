<?php

namespace App\Filter\Types;

use App\Filter\FilterTypeEnum;

class DateInterval extends AbstractFilter
{
    protected function _getType(): string
    {
        return FilterTypeEnum::DATE_INTERVAL;
    }
}
