<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

class DateUtils
{
    public static function adjustDate(\DateTimeInterface $date, bool $up): \DateTimeInterface
    {
        return (clone $date)->modify(($up ? '+' : '-').'1 day');
    }
}
