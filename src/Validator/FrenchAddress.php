<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a valid french address.
 */
#[\Attribute]
class FrenchAddress extends Constraint
{
    public string $invalidCityMessage = 'Cette ville n\'est pas une ville française valide.';
    public string $invalidFrenchPostalCodeMessage = 'Cette valeur n\'est pas un code postal français valide.';
    public string $postalCodeAndCityNameNotMatchingMessage = 'Aucune ville trouvée avec ce nom et ce code postal.';
    public string $postalCodeAndCityNotMatchingMessage = 'Ce code postal ne correspond pas à cette ville.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
