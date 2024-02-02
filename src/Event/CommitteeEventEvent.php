<?php

namespace App\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent;

class CommitteeEventEvent extends EventEvent
{
    protected $committee;

    public function __construct(?Adherent $author, CommitteeEvent $event, ?Committee $committee = null)
    {
        parent::__construct($author, $event);

        $this->committee = $committee;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function needSendMessage(): bool
    {
        return true;
    }
}
