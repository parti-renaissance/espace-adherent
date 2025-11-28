<?php

declare(strict_types=1);

namespace App\JMEFilter;

class FilterTypeEnum
{
    public const TEXT = 'text';
    public const SELECT = 'select';
    public const AUTOCOMPLETE = 'autocomplete';

    public const ZONE_AUTOCOMPLETE = 'zone_autocomplete';

    public const DATE_INTERVAL = 'date_interval';
    public const INTEGER_INTERVAL = 'integer_interval';
}
