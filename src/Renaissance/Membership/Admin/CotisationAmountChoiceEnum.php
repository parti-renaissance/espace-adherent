<?php

declare(strict_types=1);

namespace App\Renaissance\Membership\Admin;

enum CotisationAmountChoiceEnum
{
    public const AMOUNT_10 = 'amount_10';
    public const AMOUNT_30 = 'amount_30';
    public const AMOUNT_OTHER = 'amount_other';

    public const CHOICES = [
        self::AMOUNT_10,
        self::AMOUNT_30,
        self::AMOUNT_OTHER,
    ];
}
