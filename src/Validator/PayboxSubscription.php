<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PayboxSubscription extends Constraint
{
    public $message = 'La période de donation est incorrecte';

    public function validatedBy(): string
    {
        return PayboxSubscriptionValidator::class;
    }
}
