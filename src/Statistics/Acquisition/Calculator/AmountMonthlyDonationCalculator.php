<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;

class AmountMonthlyDonationCalculator extends AbstractAmountDonationCalculator
{
    public static function getPriority(): int
    {
        return 1;
    }

    public function getLabel(): string
    {
        return 'Montant dons mensuels (total)';
    }

    protected function getDonationStatus(): string
    {
        return Donation::STATUS_SUBSCRIPTION_IN_PROGRESS;
    }

    protected function getDonationDuration(): int
    {
        return PayboxPaymentSubscription::UNLIMITED;
    }
}
