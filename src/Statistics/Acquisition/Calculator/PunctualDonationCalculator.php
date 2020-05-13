<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\PayboxPaymentSubscription;
use App\Entity\Donation;

class PunctualDonationCalculator extends AbstractDonationCountCalculator
{
    public function getLabel(): string
    {
        return 'Dons ponctuels (total)';
    }

    protected function getDonationStatus(): string
    {
        return Donation::STATUS_FINISHED;
    }

    protected function getDonationDuration(): int
    {
        return PayboxPaymentSubscription::NONE;
    }
}
