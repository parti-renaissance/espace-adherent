<?php

declare(strict_types=1);

namespace App\Validator\Jecoute;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NewsContent extends Constraint
{
    public $contentLength = 10000;
    public $messageLength = 'Le contenu ne doit pas contenir plus de {{ limit }} caractères.';
    public $messageRequired = 'Le contenu est obligatoire.';
    public $path = 'content';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
