<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\TurnkeyProject;

class CitizenProjectCreationCommand extends CitizenProjectCommand
{
    /** @var Adherent */
    private $adherent;

    /** @var TurnkeyProject|null */
    private $turnkeyProject;

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->adherent = $adherent;
        $dto->phone = $adherent->getPhone();

        if ($adherent->getPostAddress()) {
            $dto->address = NullableAddress::createFromAddress($adherent->getPostAddress());
            $dto->address->setAddress(null);
        }

        return $dto;
    }

    public static function createFromAdherentAndTurnkeyProject(Adherent $adherent, TurnkeyProject $turnkeyProject): self
    {
        $dto = static::createFromAdherent($adherent);
        $dto->name = $turnkeyProject->getName();
        $dto->subtitle = $turnkeyProject->getSubtitle();
        $dto->category = $turnkeyProject->getCategory();
        $dto->problemDescription = $turnkeyProject->getProblemDescription();
        $dto->proposedSolution = $turnkeyProject->getProposedSolution();
        $dto->turnkeyProject = $turnkeyProject;

        return $dto;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getTurnkeyProject(): ?TurnkeyProject
    {
        return $this->turnkeyProject;
    }
}
