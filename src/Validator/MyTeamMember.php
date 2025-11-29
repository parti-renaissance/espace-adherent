<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MyTeamMember extends Constraint
{
    public $messageCurrentUser = 'Vous ne pouvez pas ajouter votre compte ou le compte qui vous a délégué l\'accès';
}
