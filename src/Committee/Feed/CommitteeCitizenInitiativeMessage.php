<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;

class CommitteeCitizenInitiativeMessage extends CommitteeMessage
{
    private $initiative;

    public function __construct(Adherent $author, Committee $committee, CitizenInitiative $initiative, $content = null, $published = false, $createdAt = 'now')
    {
        parent::__construct($author, $committee, $content, $published, $createdAt);
        $this->initiative = $initiative;
    }

    public function getCitizenInitiative(): CitizenInitiative
    {
        return $this->initiative;
    }
}
