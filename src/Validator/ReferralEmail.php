<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ReferralEmail extends Constraint
{
    public $message = 'referral.email_address.invalid';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
