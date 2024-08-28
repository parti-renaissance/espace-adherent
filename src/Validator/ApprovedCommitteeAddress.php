<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ApprovedCommitteeAddress extends Constraint
{
    public $errorPath = 'address';
    public $message = 'committee.address.when_approved';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
