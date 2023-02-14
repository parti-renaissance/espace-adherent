<?php

namespace App\Donation;

class DonationRequestDestinationEnum
{
    public const NATIONAL = 'national';
    public const LOCAL = 'local';

    public const ALL = [
        self::NATIONAL,
        self::LOCAL,
    ];
}
