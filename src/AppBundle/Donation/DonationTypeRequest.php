<?php

namespace AppBundle\Donation;

use AppBundle\Validator\DonationFrequency;

class DonationTypeRequest
{
    /**
     * @DonationFrequency()
     */
    private $frequency;

    public function __construct(string $frequence)
    {
        $this->frequency = $frequence;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): void
    {
        $this->frequency = $frequency;
    }
}
