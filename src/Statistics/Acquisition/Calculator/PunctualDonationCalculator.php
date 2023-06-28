<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;

class PunctualDonationCalculator extends AbstractDonationCountCalculator
{
    public static function getPriority(): int
    {
        return 6;
    }

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
