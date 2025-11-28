<?php

declare(strict_types=1);

namespace App\NationalEvent;

enum InscriptionReminderTypeEnum: string
{
    case PAYMENT_10MIN = 'payment_10';
    case PAYMENT_1H = 'payment_60';
    case PAYMENT_6H = 'payment_360';
    case PAYMENT_12H = 'payment_720';
    case PAYMENT_20H = 'payment_1200';
}
