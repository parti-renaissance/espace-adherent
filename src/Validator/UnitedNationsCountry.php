<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * United nation country constraint.
 *
 * @Annotation
 */
class UnitedNationsCountry extends Constraint
{
    const NO_SUCH_COUNTRY_ERROR = 'no_such_united_nations_country';

    protected static $errorNames = [
        self::NO_SUCH_COUNTRY_ERROR => 'NO_SUCH_COUNTRY_ERROR',
    ];

    public $message = 'Cette valeur n\'est pas un pays valide.';
}
