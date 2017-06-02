<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;

class CommitteeWasCreatedEvent extends CommitteeEvent
{
    private $creator;

    public function __construct(Committee $committee, Adherent $creator)
    {
        parent::__construct($committee);

        $this->creator = $creator;
    }

    public function getCreator(): Adherent
    {
        return $this->creator;
    }
}
