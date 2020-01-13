<?php

namespace AppBundle\Donation;

final class DonationEvents
{
    const CREATED = 'donation.created';
    const DONATOR_UPDATED = 'donator.updated';

    private function __construct()
    {
    }
}
