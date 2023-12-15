<?php

namespace App\Adhesion\Exception;

class ActivationCodeUsedException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Le code d\'activation a déjà été utilisé.');
    }
}
