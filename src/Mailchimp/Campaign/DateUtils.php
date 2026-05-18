<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

class DateUtils
{
    public static function adjustDate(\DateTimeImmutable $date, bool $up): \DateTimeImmutable
    {
        return $date->modify(($up ? '+' : '-').'1 day');
    }
}
