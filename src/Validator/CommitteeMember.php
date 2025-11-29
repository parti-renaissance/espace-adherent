<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CommitteeMember extends Constraint
{
    public $message = 'Vous ne pouvez sélectionner de comité dont vous n\'êtes pas membre.';
}
