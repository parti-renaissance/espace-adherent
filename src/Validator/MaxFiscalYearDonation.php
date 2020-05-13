<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class MaxFiscalYearDonation extends Constraint
{
    public $message = 'donation.max_fiscal_year_donation';
    public $maxDonationInCents = 750000; // Amount in cents
}
