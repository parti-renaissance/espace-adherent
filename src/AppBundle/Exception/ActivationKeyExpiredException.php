<?php

namespace AppBundle\Exception;

use AppBundle\Entity\ActivationKey;

final class ActivationKeyExpiredException extends ActivationKeyException
{
    public function __construct(ActivationKey $key, \Exception $previous = null)
    {
        $message = sprintf('Activation key %s for account %s is expired.', $key->getToken(), $key->getAdherentUuid());

        parent::__construct($key, $message, $previous);
    }
}
