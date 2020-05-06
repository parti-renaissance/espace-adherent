<?php

namespace App\Donation;

final class DonationEvents
{
    public const CREATED = 'donation.created';
    public const UPDATED = 'donation.updated';
    public const DONATOR_UPDATED = 'donator.updated';

    private function __construct()
    {
    }
}
