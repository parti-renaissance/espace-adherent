<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donator;
use Symfony\Component\Validator\Constraints as Assert;

class DonatorMergeCommand
{
    /**
     * @var Donator|null
     *
     * @Assert\NotNull
     */
    private $sourceDonator;

    /**
     * @var Donator|null
     *
     * @Assert\NotNull
     */
    private $destinationDonator;

    public function getSourceDonator(): ?Donator
    {
        return $this->sourceDonator;
    }

    public function setSourceDonator(Donator $sourceDonator): void
    {
        $this->sourceDonator = $sourceDonator;
    }

    public function getDestinationDonator(): ?Donator
    {
        return $this->destinationDonator;
    }

    public function setDestinationDonator(Donator $destinationDonator): void
    {
        $this->destinationDonator = $destinationDonator;
    }
}
