<?php

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NewsText extends Constraint
{
    public $textLength = 1000;
    public $enrichedTextLength = 10000;
    public $messageLength = 'Le texte ne doit pas contenir plus de {{ limit }} caractères.';
    public $messageRequired = 'Le texte est obligatoire.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
