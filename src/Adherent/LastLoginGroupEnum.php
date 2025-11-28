<?php

declare(strict_types=1);

namespace App\Adherent;

class LastLoginGroupEnum
{
    public const LESS_THAN_1_MONTH = 'less_than_1_month';
    public const LESS_THAN_3_MONTHS = 'less_than_3_months';
    public const LESS_THAN_1_YEAR = 'less_than_1_year';
    public const MORE_THAN_1_YEAR = 'more_than_1_year';
}
