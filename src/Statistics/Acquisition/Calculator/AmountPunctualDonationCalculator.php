<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;

class AmountPunctualDonationCalculator extends AbstractAmountDonationCalculator
{
    public static function getPriority(): int
    {
        return 4;
    }

    public function getLabel(): string
    {
        return 'Montant dons ponctuels (total)';
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
