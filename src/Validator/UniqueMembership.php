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
    public $service = UniqueMembershipValidator::class;

    public function validatedBy()
    {
        return $this->service;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
