<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PublicId extends Constraint
{
    public string $messageWrongFormat = 'Le format du code est invalide.';
    public string $messageNotFound = 'Le code est introuvable.';
}
