<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxMonthDonation extends Constraint
{
    public $message = 'donation.max_fiscal_month_donation';
    public $maxDonationInCents = 62500; // Amount in cents

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
