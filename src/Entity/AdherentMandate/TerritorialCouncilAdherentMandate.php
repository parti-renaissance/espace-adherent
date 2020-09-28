<?php

namespace App\Entity\AdherentMandate;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository")
 */
class TerritorialCouncilAdherentMandate extends AbstractAdherentMandate
{
    /**
     * @var TerritorialCouncil
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\TerritorialCouncil\TerritorialCouncil")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $territorialCouncil;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank(message="common.quality.invalid_choice")
     * @Assert\Choice(choices=App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS, strict=true)
     */
    private $quality;

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

    public function getTerritorialCouncil(): TerritorialCouncil
    {
        return $this->territorialCouncil;
    }

    public function setTerritorialCouncil(TerritorialCouncil $territorialCouncil): void
    {
        $this->territorialCouncil = $territorialCouncil;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
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
