<?php

namespace App\AdherentMessage\Segment;

use App\AdherentMessage\DynamicSegmentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DynamicSegmentEvent extends Event
{
    private $segment;

    public function __construct(DynamicSegmentInterface $segment)
    {
        $this->segment = $segment;
    }

    public function getSegment(): DynamicSegmentInterface
    {
        return $this->segment;
    }
}
