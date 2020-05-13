<?php

namespace App\Committee;

use App\Collection\AdherentCollection;

class MultipleReferentsFoundException extends \LogicException
{
    private $referents;

    public function __construct(AdherentCollection $referents, $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->referents = $referents;
    }

    public function getReferents(): AdherentCollection
    {
        return $this->referents;
    }
}
