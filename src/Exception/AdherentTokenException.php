<?php

namespace App\Exception;

use App\Entity\AdherentExpirableTokenInterface;

class AdherentTokenException extends \RuntimeException
{
    private $token;

    public function __construct(AdherentExpirableTokenInterface $token, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->token = $token;
    }

    public function getToken(): AdherentExpirableTokenInterface
    {
        return $this->token;
    }
}
