<?php

namespace App\JMEFilter\Types;

use App\JMEFilter\FilterTypeEnum;

class DateInterval extends AbstractFilter
{
    protected function _getType(): string
    {
        return FilterTypeEnum::DATE_INTERVAL;
    }
}
