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
     * @var string|null
     *
     * @ORM\Column(length=255, nullable=true)
     *
     * @Assert\NotBlank(message="common.quality.invalid_choice")
     * @Assert\Choice(choices=App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS, strict=true)
     */
    protected $quality;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdditionallyElected;

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
