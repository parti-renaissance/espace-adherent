<?php

namespace App\Donation;

use Doctrine\ORM\EntityManagerInterface;

class DonatorMergeCommandHandler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handle(DonatorMergeCommand $donatorMergeCommand): void
    {
        $sourceDonator = $donatorMergeCommand->getSourceDonator();
        $destinationDonator = $donatorMergeCommand->getDestinationDonator();

        foreach ($sourceDonator->getDonations() as $donation) {
            $sourceDonator->removeDonation($donation);
            $donation->setDonator($destinationDonator);
        }

        $this->em->remove($sourceDonator);
        $this->em->flush();
    }
}
