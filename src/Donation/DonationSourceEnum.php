<?php

namespace App\Donation;

use MyCLabs\Enum\Enum;

class DonationSourceEnum extends Enum
{
    public const DONATION = 'donation';
    public const MEMBERSHIP = 'membership';
}
