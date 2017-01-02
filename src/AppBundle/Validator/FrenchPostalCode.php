<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * French postal code constraint.
 *
 * @Annotation
 */
class FrenchPostalCode extends Constraint
{
    const NO_SUCH_POSTAL_CODE = 'no_such_postal_code';

    protected static $errorNames = array(
        self::NO_SUCH_POSTAL_CODE => 'NO_SUCH_POSTAL_CODE',
    );

    public $message = 'Cette valeur n\'est pas un code postal français valide.';
}
