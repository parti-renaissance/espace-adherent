<?php

namespace App\Adhesion\Exception;

class ActivationCodeNotFoundException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Le code d\'activation est erroné.');
    }
}
