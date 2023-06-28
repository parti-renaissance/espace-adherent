<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;

class PunctualAdherentDonationCalculator extends AbstractDonationCountCalculator
{
    public static function getPriority(): int
    {
        return 5;
    }

    public function getLabel(): string
    {
        return 'Dons ponctuels par des adhérents (total)';
    }

    protected function getDonationStatus(): string
    {
        return Donation::STATUS_FINISHED;
    }

    protected function getDonationDuration(): int
    {
        return PayboxPaymentSubscription::NONE;
    }

    protected function isAdherentOnly(): bool
    {
        return true;
    }
}
