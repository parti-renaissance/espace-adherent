<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueDonationSubscription extends Constraint
{
    public $message = 'donation.subscription.not_unique';
    public $messageForAnonymous = 'donation.subscription.not_unique_from_anonymous';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
