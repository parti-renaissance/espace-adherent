<?php

declare(strict_types=1);

namespace App\Adhesion\Exception;

class ActivationCodeRevokedException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Le code d\'activation n\'est plus valide.');
    }
}
