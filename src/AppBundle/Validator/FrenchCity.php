<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * French city constraint.
 *
 * @Annotation
 */
class FrenchCity extends Constraint
{
    const NO_SUCH_CITY = 'no_such_city';

    protected static $errorNames = array(
        self::NO_SUCH_CITY => 'NO_SUCH_CITY',
    );

    public $message = 'Cette valeur n\'est pas un identifiant de ville française valide.';
}
