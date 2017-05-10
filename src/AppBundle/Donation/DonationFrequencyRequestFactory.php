<?php

namespace AppBundle\Donation;

class DonationFrequencyRequestFactory
{
    public function create(string $frequency): DonationTypeRequest
    {
        return new DonationTypeRequest($frequency);
    }
}
