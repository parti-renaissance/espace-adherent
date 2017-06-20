<?php

namespace AppBundle\Summary;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Summary;
use Cocur\Slugify\SlugifyInterface;

class SummaryFactory
{
    private $slugger;

    public function __construct(SlugifyInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function createFromAdherent(Adherent $adherent): Summary
    {
        return Summary::createFromMember($adherent, $this->slugger->slugify($adherent->getFullName()));
    }
}
