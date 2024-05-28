<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Repository\AdherentMandate\NationalCouncilAdherentMandateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NationalCouncilAdherentMandateRepository::class)]
class NationalCouncilAdherentMandate extends AbstractAdherentMandate
{
    public static function create(
        TerritorialCouncil $coTerrParis,
        Adherent $adherent,
        ?\DateTime $date = null,
        ?string $gender = null,
        ?string $quality = null
    ): self {
        $mandate = new self($adherent, $gender ?? $adherent->getGender(), $date ?? new \DateTime(), null, $quality);
        $mandate->setTerritorialCouncil($coTerrParis);

        return $mandate;
    }
}
