<?php

namespace App\Adhesion\Exception;

class ActivationCodeLimitReachedException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Vous avez atteint le nombre maximum de demandes de code. Veuillez réessayer plus tard.');
    }
}
