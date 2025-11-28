<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class FrenchAddressOrNationalityDonation extends Constraint
{
    public $message = 'donation.french_address_or_nationality_donation';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
