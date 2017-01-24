<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * City is associated to postal code constraint.
 *
 * @Annotation
 */
class CityAssociatedToPostalCode extends Constraint
{
    const CITY_NOT_ASSOCIATED_TO_POSTAL_CODE = 'city_not_associated_to_postal_code';

    protected static $errorNames = array(
        self::CITY_NOT_ASSOCIATED_TO_POSTAL_CODE => 'CITY_NOT_ASSOCIATED_TO_POSTAL_CODE',
    );

    public $message = 'Cette ville et ce code postal ne sont pas liés.';
    public $errorPath = 'postalCode';
    public $postalCodeField = 'postalCode';
    public $cityField = 'city';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
