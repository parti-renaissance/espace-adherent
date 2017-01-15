<?php

namespace AppBundle\Exception;

use AppBundle\Entity\ActivationKey;

class ActivationKeyException extends \RuntimeException
{
    private $activationKey;

    public function __construct(ActivationKey $key, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->activationKey = $key;
    }

    public function getActivationKey(): ActivationKey
    {
        return $this->activationKey;
    }
}
