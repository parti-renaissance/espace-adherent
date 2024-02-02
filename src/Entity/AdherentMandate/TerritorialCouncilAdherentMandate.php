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
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank(message="common.quality.invalid_choice")
     * @Assert\Choice(choices=App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS)
     */
    protected $quality;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAdditionallyElected = false;

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

    public function isAdditionallyElected(): bool
    {
        return $this->isAdditionallyElected;
    }

    public function setIsAdditionallyElected(bool $isAdditionallyElected): void
    {
        $this->isAdditionallyElected = $isAdditionallyElected;
    }
}
