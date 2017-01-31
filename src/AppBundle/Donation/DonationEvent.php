<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Symfony\Component\EventDispatcher\Event;

class DonationEvent extends Event
{
    private $donation;

    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
    }

    public function getDonation(): Donation
    {
        return $this->donation;
    }
}
