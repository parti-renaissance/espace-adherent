<?php

declare(strict_types=1);

namespace App\Donation\Event;

use App\Entity\Donator;
use Symfony\Contracts\EventDispatcher\Event;

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
