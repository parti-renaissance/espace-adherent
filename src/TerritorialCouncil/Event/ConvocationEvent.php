<?php

namespace App\TerritorialCouncil\Event;

use App\Entity\TerritorialCouncil\Convocation;
use Symfony\Component\EventDispatcher\Event;

class ConvocationEvent extends Event
{
    private $convocation;

    public function __construct(Convocation $convocation)
    {
        $this->convocation = $convocation;
    }

    public function getConvocation(): Convocation
    {
        return $this->convocation;
    }
}
