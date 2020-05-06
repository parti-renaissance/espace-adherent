<?php

namespace App\Exception;

use App\Entity\AdherentExpirableTokenInterface;

final class AdherentTokenAlreadyUsedException extends AdherentTokenException
{
    public function __construct(AdherentExpirableTokenInterface $token, \Exception $previous = null)
    {
        $message = sprintf('The %s token %s was already used on %s.', $token->getType(), $token->getValue(), $token->getUsageDate()->format(\DATE_ISO8601));

        parent::__construct($token, $message, $previous);
    }
}
