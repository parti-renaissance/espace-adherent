<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class FrenchAddressOrNationalityDonation extends Constraint
{
    public $message = 'donation.french_address_or_nationality_donation';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
