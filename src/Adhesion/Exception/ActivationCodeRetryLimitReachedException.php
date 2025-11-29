<?php

declare(strict_types=1);

namespace App\Adhesion\Exception;

class ActivationCodeRetryLimitReachedException extends AbstractActivationCodeException
{
    public function __construct()
    {
        parent::__construct('Veuillez patienter quelques minutes avant de retenter.');
    }
}
