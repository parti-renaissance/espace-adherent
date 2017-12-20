<?php

namespace AppBundle\CitizenProject;

use AppBundle\Address\NullableAddress;
use AppBundle\Entity\Adherent;
use AppBundle\Validator\UniqueCitizenProject as AssertUniqueCitizenProject;

/**
 * @AssertUniqueCitizenProject
 */
class CitizenProjectCreationCommand extends CitizenProjectCommand
{
    /** @var Adherent */
    private $adherent;

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

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
