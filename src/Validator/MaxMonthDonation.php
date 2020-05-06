<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class MaxMonthDonation extends Constraint
{
    public $message = 'donation.max_fiscal_month_donation';
    public $maxDonationInCents = 62500; // Amount in cents

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
