<?php

declare(strict_types=1);

namespace App\Donation\Request;

class DonationRequestDestinationEnum
{
    public const NATIONAL = 'national';
    public const LOCAL = 'local';

    public const ALL = [
        self::NATIONAL,
        self::LOCAL,
    ];
}
