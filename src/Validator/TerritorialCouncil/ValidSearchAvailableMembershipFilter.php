<?php

namespace App\Validator\TerritorialCouncil;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidSearchAvailableMembershipFilter extends Constraint
{
    public $messageEmptyQuery = 'Veuillez utiliser la recherche pour retrouver votre binôme';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
