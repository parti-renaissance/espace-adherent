<?php

namespace AppBundle\MunicipalManager;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\City;

class MunicipalManagerAssociationValueObject
{
    /**
     * @var City
     */
    private $city;

    /**
     * @var Adherent|null
     */
    private $adherent;

    public function __construct(City $city, Adherent $adherent = null)
    {
        $this->city = $city;
        $this->adherent = $adherent;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): void
    {
        $this->city = $city;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }
}
