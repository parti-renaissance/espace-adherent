<?php

namespace AppBundle\AdherentMessage\Command;

use AppBundle\Mailchimp\SynchronizeMessageInterface;

class SynchronizeAdherentSegmentCommand implements SynchronizeMessageInterface
{
    private $adherentSegmentId;

    public function __construct(int $adherentSegmentId)
    {
        $this->adherentSegmentId = $adherentSegmentId;
    }

    public function getAdherentSegmentId(): int
    {
        return $this->adherentSegmentId;
    }
}
