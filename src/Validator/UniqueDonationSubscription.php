<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueDonationSubscription extends Constraint
{
    public $message = 'donation.subscription.not_unique';
    public $messageForAnonymous = 'donation.subscription.not_unique_from_anonymous';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
