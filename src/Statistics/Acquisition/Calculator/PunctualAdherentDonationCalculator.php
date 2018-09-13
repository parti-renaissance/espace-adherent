<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;

class PunctualAdherentDonationCalculator extends AbstractDonationCountCalculator
{
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
