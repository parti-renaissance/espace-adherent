<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the Unique Membership validator.
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueMembership extends Constraint
{
    public $message = 'adherent.email_address.not_unique';
    public $path = 'emailAddress';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
