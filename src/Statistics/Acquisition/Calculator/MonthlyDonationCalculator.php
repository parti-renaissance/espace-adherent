<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;

class MonthlyDonationCalculator extends AbstractDonationCountCalculator
{
    public static function getPriority(): int
    {
        return 3;
    }

    public function getLabel(): string
    {
        return 'Dons mensuels (total)';
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
