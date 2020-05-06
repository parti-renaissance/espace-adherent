<?php

namespace App\Exception;

use App\Entity\AdherentExpirableTokenInterface;

final class AdherentTokenExpiredException extends AdherentTokenException
{
    public function __construct(AdherentExpirableTokenInterface $token, \Exception $previous = null)
    {
        $message = sprintf('The %s token %s for account %s is expired.', $token->getType(), $token->getValue(), $token->getAdherentUuid());

        parent::__construct($token, $message, $previous);
    }
}
