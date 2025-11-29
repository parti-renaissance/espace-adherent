<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AdherentUuid extends Constraint
{
    public $message = 'adherent.uuid.adherent_not_found';
}
