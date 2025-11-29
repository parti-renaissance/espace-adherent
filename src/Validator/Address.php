<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Address extends Constraint
{
    public $frenchPostalCodeMessage = 'Cette valeur n\'est pas un code postal français valide.';
    public $frenchCityMessage = 'Cette ville n\'est pas une ville française valide.';
    public $notAssociatedMessage = 'Ce code postal ne correspond pas à cette ville.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
