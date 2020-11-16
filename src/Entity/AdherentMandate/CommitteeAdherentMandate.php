<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\CommitteeAdherentMandateRepository")
 */
class CommitteeAdherentMandate extends AbstractAdherentMandate
{
    public function __construct(
        Adherent $adherent,
        string $gender,
        Committee $committee,
        \DateTime $beginAt,
        \DateTime $finishAt = null
    ) {
        parent::__construct($adherent, $gender, $beginAt, $finishAt);

        $this->committee = $committee;
    }
}
