<?php

namespace AppBundle\Exception;

class AdherentCollectionException extends \BadMethodCallException
{
    public function __construct(
        $message = 'This method requires a collection of Adherent entities',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
