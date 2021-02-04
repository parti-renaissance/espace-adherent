<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class ApprovedCommitteeAddress extends Constraint
{
    public $errorPath = 'address';
    public $message = 'committee.address.when_approved';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
