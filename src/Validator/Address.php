<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a valid address.
 *
 * @Annotation
 */
class Address extends Constraint
{
    const INVALID_ADDRESS = 'invalid_city';

    protected static $errorNames = [
        self::INVALID_ADDRESS => 'INVALID_ADDRESS',
    ];

    public $frenchPostalCodeMessage = 'Cette valeur n\'est pas un code postal français valide.';
    public $frenchCityMessage = 'Cette ville n\'est pas une ville française valide.';
    public $notAssociatedMessage = 'Ce code postal ne correspond pas à cette ville.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
