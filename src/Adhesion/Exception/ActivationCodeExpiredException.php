<?php

namespace App\Adhesion\Exception;

class ActivationCodeExpiredException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Le code d\'activation a expiré.');
    }
}
