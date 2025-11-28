<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class BannedAdherent extends Constraint
{
    public $message = 'Oups, quelque chose s\'est mal passé';
}
