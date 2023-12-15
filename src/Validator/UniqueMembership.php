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
    public string $message = 'adherent.email_address.not_unique';
    public string $path = 'emailAddress';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
