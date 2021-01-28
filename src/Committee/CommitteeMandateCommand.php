<?php

namespace App\Committee;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Validator\AdherentForCommitteeMandateReplacement as AssertAdherentValid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertAdherentValid(errorPath="adherent")
 */
class CommitteeMandateCommand
{
    /**
     * @var Adherent|null
     *
     * @Assert\NotNull(message="adherent_mandate.adherent.empty")
     */
    protected $adherent;

    /**
     * @var string|null
     */
    private $gender;

    /**
     * @var \DateTime|null
     */
    protected $beginAt;

    /**
     * @var Committee
     */
    protected $committee;

    /**
     * @var string|null
     */
    protected $quality;

    public $provisional;

    public static function createFromCommitteeMandate(CommitteeAdherentMandate $mandate): self
    {
        $dto = new self();
        $dto->gender = $mandate->getGender();
        $dto->committee = $mandate->getCommittee();
        $dto->quality = $mandate->getQuality();
        $dto->provisional = (bool) $mandate->isSupervisor();
        $dto->beginAt = new \DateTime();

        return $dto;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function isProvisional(): bool
    {
        return $this->provisional;
    }

    public function getBeginAt(): \DateTime
    {
        return $this->beginAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }
}
