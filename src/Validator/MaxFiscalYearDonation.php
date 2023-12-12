<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class MaxFiscalYearDonation extends Constraint
{
    public string $message = 'donation.max_fiscal_year_donation';
    public int $maxDonationInCents = 750000; // Amount in cents
    public ?string $path = null;

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
