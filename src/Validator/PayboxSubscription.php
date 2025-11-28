<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PayboxSubscription extends Constraint
{
    public $message = 'La période de donation est incorrecte';

    public function validatedBy(): string
    {
        return PayboxSubscriptionValidator::class;
    }
}
