<?php

namespace AppBundle\Donation;

final class DonationEvents
{
    public const CREATED = 'donation.created';
    public const DONATOR_UPDATED = 'donator.updated';

    private function __construct()
    {
    }
}
