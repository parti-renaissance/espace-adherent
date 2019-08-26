<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\Donation;

class DonationListener
{
    public function preUpdate(Donation $donation): void
    {
        if (!\in_array($donation->getStatus(), [Donation::STATUS_REFUNDED, Donation::STATUS_WAITING_CONFIRMATION])
            && $donator = $donation->getDonator()
        ) {
            $donator->setLastDonationAt($donation->getCreatedAt());
        }
    }
}
