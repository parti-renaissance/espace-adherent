<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Validator\UniqueCitizenProject as AssertUniqueCitizenProject;

/**
 * @AssertUniqueCitizenProject
 */
class CitizenProjectCreationCommand extends CitizenProjectCommand
{
    /** @var Adherent */
    private $adherent;

    public function __construct(Address $address = null)
    {
        parent::__construct($address);
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->adherent = $adherent;

        return $dto;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setCitizenProject(CitizenProject $citizenProject): void
    {
        $this->citizenProject = $citizenProject;
    }
}
