<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository")
 */
class TerritorialCouncilAdherentMandate extends AbstractAdherentMandate
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdditionallyElected = false;

    public function __construct(
        Adherent $adherent,
        TerritorialCouncil $territorialCouncil,
        string $quality,
        string $gender,
        \DateTime $beginAt,
        \DateTime $finishAt = null,
        bool $isAdditionallyElected = false
    ) {
        parent::__construct($adherent, $gender, $beginAt, $finishAt);

        $this->territorialCouncil = $territorialCouncil;
        $this->quality = $quality;
        $this->isAdditionallyElected = $isAdditionallyElected;
    }

    public function isAdditionallyElected(): bool
    {
        return $this->isAdditionallyElected;
    }

    public function setIsAdditionallyElected(bool $isAdditionallyElected): void
    {
        $this->isAdditionallyElected = $isAdditionallyElected;
    }
}
