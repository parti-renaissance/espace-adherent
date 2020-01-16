<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donator;
use Symfony\Component\EventDispatcher\Event;

class DonatorEvent extends Event
{
    private $donator;

    public function __construct(Donator $donator)
    {
        $this->donator = $donator;
    }

    public function getDonator(): Donator
    {
        return $this->donator;
    }
}
